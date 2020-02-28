<?php

namespace TelegramOSINT\TGConnection\SocketMessenger;

use LogicException;
use TelegramOSINT\Auth\AES\AES;
use TelegramOSINT\Auth\AES\PhpSecLibAES;
use TelegramOSINT\Client\AuthKey\AuthKey;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\Logger\Logger;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\MTSerialization\MTDeserializer;
use TelegramOSINT\MTSerialization\OwnImplementation\OwnDeserializer;
use TelegramOSINT\TGConnection\DataCentre;
use TelegramOSINT\TGConnection\Socket\Socket;
use TelegramOSINT\TGConnection\SocketMessenger\EncryptedSocketCallbacks\CallbackMessageListener;
use TelegramOSINT\TGConnection\SocketMessenger\MessengerTools\MessageIdGenerator;
use TelegramOSINT\TGConnection\SocketMessenger\MessengerTools\OuterHeaderWrapper;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\get_state;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\msgs_ack;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\updates_get_difference;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\BadServerSalt;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\MsgContainer;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Rpc\Errors\FloodWait;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Rpc\RpcError;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Rpc\RpcResult;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Update\UpdatesTooLong;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\UpdatesState;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * Encrypted Telegram-specific socket
 */
class EncryptedSocketMessenger extends TgSocketMessenger implements SocketMessenger
{
    /**
     * @var int
     */
    private $msg_seqno;
    /**
     * @var MessageIdGenerator
     */
    private $msgIdGenerator;
    /**
     * @var string
     */
    private $salt;
    /**
     * @var string
     */
    private $sessionId;
    /**
     * @var string
     */
    private $authKeyId;
    /**
     * @var string
     */
    private $authKey;
    /**
     * @var AuthKey
     */
    private $authKeyObj;
    /**
     * @var AES
     */
    private $aes;
    /**
     * @var TLClientMessage[]
     */
    private $sentMessages = [];
    /**
     * @var MTDeserializer
     */
    private $deserializer;
    /**
     * @var OuterHeaderWrapper
     */
    private $outerHeaderWrapper;
    /**
     * @var MessageListener
     */
    private $messageReceiptCallback;
    /**
     * @var MessageListener[]
     */
    private $rpcMessages = [];
    /**
     * @var AnonymousMessage[]
     */
    private $reportableMessageQueue = [];
    /**
     * @var AnonymousMessage[]
     */
    private $messagesToBeProcessedQueue = [];

    /**
     * @param Socket          $socket
     * @param AuthKey         $authKey
     * @param MessageListener $callback
     */
    public function __construct(Socket $socket, AuthKey $authKey, MessageListener $callback)
    {
        parent::__construct($socket);
        $this->messageReceiptCallback = $callback;

        $this->msg_seqno = 0;

        $this->salt = openssl_random_pseudo_bytes(8);
        $this->sessionId = openssl_random_pseudo_bytes(8);
        $this->authKey = $authKey->getRawAuthKey();
        $this->authKeyId = substr(sha1($this->authKey, true), -8);
        $this->authKeyObj = $authKey;

        $this->aes = new PhpSecLibAES();
        $this->outerHeaderWrapper = new OuterHeaderWrapper();
        $this->msgIdGenerator = new MessageIdGenerator();
        $this->deserializer = new OwnDeserializer();
    }

    /**
     * @throws TGException
     *
     * @return AnonymousMessage
     */
    public function readMessage()
    {
        if (!empty($this->messagesToBeProcessedQueue)) {
            $this->processServiceMessage(array_shift($this->messagesToBeProcessedQueue));
        } elseif (empty($this->reportableMessageQueue)) {
            if ($msg = $this->readMessageFromSocket()) {
                $this->processServiceMessage($msg);
            }
        }

        return $this->reportMessageToSubscriber();
    }

    /**
     * @throws TGException
     */
    protected function readMessageFromSocket(): ?AnonymousMessage
    {
        $packet = $this->readPacket();
        if (!$packet) {
            return null;
        }

        return $this->deserializePayload(
            $this->decodeDecryptedPayloadHeaders(
                $this->decryptPayload(
                    $this->outerHeaderWrapper->unwrap(
                        $packet
                    )
                )
            )
        );
    }

    /**
     * @param TLClientMessage $message
     * @param callable        $onAsyncResponse function(AnonymousMessage $message)
     *
     * @throws TGException
     */
    public function getResponseAsync(TLClientMessage $message, callable $onAsyncResponse)
    {
        $messageId = $this->msgIdGenerator->generateNext();
        $this->writeIdentifiedMessage($message, $messageId);
        $this->rpcMessages[$messageId] = new CallbackMessageListener($onAsyncResponse);
    }

    /**
     * @param string $payload
     *
     * @throws TGException
     *
     * @return string
     */
    private function decryptPayload(string $payload)
    {
        $authKeyId = substr($payload, 0, 8);
        $msgKey = substr($payload, 8, 16);
        $payload = substr($payload, 24);

        if(strcmp($authKeyId, $this->authKeyId) != 0)
            throw new TGException(TGException::ERR_TL_CONTAINER_BAD_AUTHKEY_ID);
        list($aes_key, $aes_iv) = $this->aes_calculate($msgKey, $this->authKey, false);
        $decryptedPayload = $this->aes->decryptIgeMode($payload, $aes_key, $aes_iv);

        $myMsgKey = substr(hash('sha256', substr($this->authKey, 96, 32).$decryptedPayload, true), 8, 16);
        if(strcmp($msgKey, $myMsgKey) != 0)
            throw new TGException(TGException::ERR_TL_CONTAINER_BAD_MSG_KEY);

        return $decryptedPayload;
    }

    /**
     * @param string $decryptedPayload
     *
     * @throws TGException
     *
     * @return bool|string
     */
    private function decodeDecryptedPayloadHeaders(string $decryptedPayload)
    {
        // we do not use this salt, we request new one every time
        // $server_salt = substr($decryptedPayload, 0, 8);
        $session_id = substr($decryptedPayload, 8, 8);
        if(strcmp($session_id, $this->sessionId) != 0)
            throw new TGException(TGException::ERR_TL_CONTAINER_BAD_SESSION_ID);
        $msg_id = substr($decryptedPayload, 16, 8);
        $msg_id = unpack('Q', $msg_id)[1];
        $seq_no = substr($decryptedPayload, 24, self::HEADER_LENGTH_BYTES);
        $seq_no = unpack('I', $seq_no);

        if($seq_no % 2 == 1)
            $this->acknowledgeReceipt($msg_id);

        $message_data_length = unpack('V', substr($decryptedPayload, 28, self::HEADER_LENGTH_BYTES))[1];

        return substr($decryptedPayload, 32, $message_data_length);
    }

    /**
     * @param int $msgId
     *
     * @throws TGException
     */
    private function acknowledgeReceipt($msgId)
    {
        $this->writeMessage(new msgs_ack([$msgId]));
    }

    /**
     * @param string $payload
     *
     * @throws TGException
     *
     * @return AnonymousMessage
     */
    private function deserializePayload(string $payload)
    {
        Logger::log('Read_Message_Binary', bin2hex($payload));
        $deserializedMessage = $this->deserializer->deserialize($payload);
        Logger::log('Read_Message_TL', $deserializedMessage->getDebugPrintable());

        return $deserializedMessage;
    }

    /**
     * @return AnonymousMessage|null
     */
    private function reportMessageToSubscriber()
    {
        $message = array_shift($this->reportableMessageQueue);
        if($message)
            $this->messageReceiptCallback->onMessage($message);

        return $message;
    }

    /**
     * @param AnonymousMessage $message
     *
     * @throws TGException
     */
    final private function processServiceMessage(AnonymousMessage $message)
    {
        // rpc
        if(RpcResult::isIt($message)) {
            $rpcResult = new RpcResult($message);
            $msgRequestId = $rpcResult->getRequestMsgId();
            $result = $rpcResult->getResult();

            if(RpcError::isIt($result))
                $this->analyzeRpcError(new RpcError($result));

            if (isset($this->rpcMessages[$msgRequestId])) {
                $callback = $this->rpcMessages[$msgRequestId];
                unset($this->rpcMessages[$msgRequestId]);
                $callback->onMessage($result);
            }

            $this->reportableMessageQueue[] = $result;
        }

        // container of messages
        elseif (MsgContainer::isIt($message)){
            $msgContainer = new MsgContainer($message);
            // collect messages for further processes: one message process per read
            $this->messagesToBeProcessedQueue = $msgContainer->getMessages();
        }

        // salt change
        elseif(BadServerSalt::isIt($message)){
            $badServerSalt = new BadServerSalt($message);
            $this->reSendWithUpdatedSalt($badServerSalt->getNewServerSalt(), $badServerSalt->getBadMsdId());
        }

        elseif(UpdatesTooLong::isIt($message)){
            $this->getResponseAsync(new get_state(), function (AnonymousMessage $response) {
                $updatesState = new UpdatesState($response);
                $this->getResponseAsync(new updates_get_difference(
                    $updatesState->getPts(),
                    $updatesState->getQts(),
                    $updatesState->getDate()
                ), function (AnonymousMessage $message) {
                    //
                });
            });
        }

        else {
            $this->reportableMessageQueue[] = $message;
        }
    }

    /**
     * @param RpcError $rpcError
     *
     * @throws TGException
     */
    private function analyzeRpcError(RpcError $rpcError)
    {
        $parts = explode(':', $this->authKeyObj->getSerializedAuthKey());
        $userId = $parts[0];

        if($rpcError->isNetworkMigrateError())
            throw new TGException(TGException::ERR_MSG_NETWORK_MIGRATE, "reconnection to another DataCenter needed for $userId");
        if($rpcError->isPhoneMigrateError())
            throw new TGException(TGException::ERR_MSG_PHONE_MIGRATE, "phone $userId already used in another DataCenter");
        if($rpcError->isFloodError())
            throw new TGException(TGException::ERR_MSG_FLOOD, (new FloodWait($rpcError))->getWaitTimeSec());
        if($rpcError->isUserDeactivated())
            throw new TGException(TGException::ERR_MSG_USER_BANNED, "User $userId banned");
        if($rpcError->isAuthKeyUnregistered())
            throw new TGException(TGException::ERR_MSG_USER_BANNED, "User $userId unregistered");
        if($rpcError->isPhoneBanned())
            throw new TGException(TGException::ERR_MSG_PHONE_BANNED, "User $userId phone banned");
        if($rpcError->isAuthKeyDuplicated())
            throw new TGException(TGException::ERR_MSG_BANNED_AUTHKEY_DUPLICATED, "relogin with phone number needed $userId");
        if($rpcError->isSessionRevoked())
            throw new TGException(TGException::ERR_MSG_BANNED_SESSION_STOLEN, "bot stolen by revoking session $userId");
    }

    /**
     * @param int    $new_salt
     * @param string $badMessageId
     *
     * @throws TGException
     */
    private function reSendWithUpdatedSalt($new_salt, $badMessageId)
    {
        $this->salt = $new_salt;

        if(!isset($this->sentMessages[$badMessageId]))
            throw new TGException(TGException::ERR_MSG_RESEND_IMPOSSIBLE);
        $badMessage = $this->sentMessages[$badMessageId];
        $newMessageId = $this->msgIdGenerator->generateNext();

        if(isset($this->rpcMessages[$badMessageId])){
            $this->rpcMessages[$newMessageId] = $this->rpcMessages[$badMessageId];
            unset($this->rpcMessages[$badMessageId]);
        }

        $this->writeIdentifiedMessage($badMessage, $newMessageId);
        unset($this->sentMessages[$badMessageId]);

        $this->readMessage();
    }

    /**
     * @param TLClientMessage $payload
     *
     * @throws TGException
     */
    public function writeMessage(TLClientMessage $payload)
    {
        $messageId = $this->msgIdGenerator->generateNext();
        $this->writeIdentifiedMessage($payload, $messageId);
    }

    /**
     * @param TLClientMessage $payload
     * @param int             $messageId
     *
     * @throws TGException
     */
    protected function writeIdentifiedMessage(TLClientMessage $payload, $messageId)
    {
        Logger::log('Write_Message_Binary', bin2hex($payload->toBinary()));
        Logger::log('Write_Message_ID', $messageId);
        Logger::log('Write_Message_TL', $this->deserializer->deserialize($payload->toBinary())->getDebugPrintable());

        $binaryPayload = $this->outerHeaderWrapper->wrap(
            $this->wrapEncryptedContainer(
                $this->wrapEncryptedData($payload, $messageId, $this->isContentRelatedPayload($payload))
            )
        );

        $this->socket->writeBinary($binaryPayload);
        $this->sentMessages[$messageId] = $payload;
    }

    private function isContentRelatedPayload(TLClientMessage $payload)
    {
        $nonContentRelatedNames = [
            'rpc_result',
            //'rpc_error',
            'rpc_drop_answer',
            'rpc_answer_unknown',
            'rpc_answer_dropped_running',
            'rpc_answer_dropped',
            'get_future_salts',
            'future_salt',
            'future_salts',
            'ping',
            'pong',
            'ping_delay_disconnect',
            'destroy_session',
            'destroy_session_ok',
            'destroy_session_none',
            //'new_session_created',
            'usual_msg_container_deserialization',
            'msg_copy',
            'gzip_packed',
            'http_wait',
            'msgs_ack',
            'bad_msg_notification',
            'bad_server_salt',
            'msgs_state_req',
            'msgs_state_info',
            'msgs_all_info',
            'msg_detailed_info',
            'msg_new_detailed_info',
            'msg_resend_req',
            'msg_resend_ans_req',
        ];

        return !in_array($payload->getName(), $nonContentRelatedNames);
    }

    /**
     * @param TLClientMessage $payload
     * @param int             $messageId
     * @param bool            $contentRelated
     *
     * @return string
     */
    private function wrapEncryptedData(TLClientMessage $payload, $messageId, $contentRelated)
    {
        $seq_no = $this->generate_msg_seqno($contentRelated);

        $payload = $payload->toBinary();
        $length = strlen($payload);

        $padding = $this->calcRemainder(-$length, 16);
        if ($padding < 12) {
            $padding += 16;
        }
        $padding = openssl_random_pseudo_bytes($padding);

        return
            $this->salt.
            $this->sessionId.
            pack('Q', $messageId).
            pack('VV', $seq_no, $length).
            $payload.
            $padding;
    }

    /**
     * @param int $a
     * @param int $b
     *
     * @return float|int
     */
    private function calcRemainder(int $a, int $b)
    {
        $remainder = $a % $b;
        if ($remainder < 0)
            $remainder += abs($b);

        return $remainder;
    }

    private function aes_calculate($msg_key, $auth_key, $to_server = true)
    {
        $x = $to_server ? 0 : 8;
        $sha256_a = hash('sha256', $msg_key.substr($auth_key, $x, 36), true);
        $sha256_b = hash('sha256', substr($auth_key, 40 + $x, 36).$msg_key, true);
        $aes_key = substr($sha256_a, 0, 8).substr($sha256_b, 8, 16).substr($sha256_a, 24, 8);
        $aes_iv = substr($sha256_b, 0, 8).substr($sha256_a, 8, 16).substr($sha256_b, 24, 8);

        return [$aes_key, $aes_iv];
    }

    /**
     * @param string $payload
     *
     * @throws TGException
     *
     * @return string
     */
    private function wrapEncryptedContainer($payload)
    {
        $msg_key_large = hash('sha256', substr($this->authKey, 88, 32).$payload, true);
        $msgKey = substr($msg_key_large, 8, 16);

        list($aes_key, $aes_iv) = $this->aes_calculate($msgKey, $this->authKey);
        $encryptedPayload = $this->aes->encryptIgeMode($payload, $aes_key, $aes_iv);

        return
            $this->authKeyId.
            $msgKey.
            $encryptedPayload;
    }

    /**
     * Generate context-bound sequence number
     *
     * @param bool $context_related
     *
     * @return int
     */
    private function generate_msg_seqno($context_related) {
        $in = $context_related ? 1 : 0;
        //multiply by two and add one, if context-related
        $value = ($this->msg_seqno * 2) + $in;
        //increase current $this->seq_no if context related
        $this->msg_seqno += $in;

        return $value;
    }

    /**
     * @return DataCentre
     */
    public function getDCInfo()
    {
        return $this->socket->getDCInfo();
    }

    /**
     * @return void
     */
    public function terminate()
    {
        $this->socket->terminate();
    }

    /**
     * @param TLClientMessage[] $messages
     * @param callable          $onLastResponse function(AnonymousMessage $message)
     */
    public function getResponseConsecutive(array $messages, callable $onLastResponse)
    {
        $messages = array_reverse($messages);
        if (!$messages) {
            throw new LogicException('empty messages');
        }
        $newFunc = $onLastResponse;
        foreach ($messages as $message) {
            $newFunc = function () use ($message, $newFunc) {
                $this->getResponseAsync($message, $newFunc);
            };
        }
        $newFunc();
    }
}
