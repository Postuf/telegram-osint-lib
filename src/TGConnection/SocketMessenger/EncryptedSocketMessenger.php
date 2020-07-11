<?php

declare(strict_types=1);

namespace TelegramOSINT\TGConnection\SocketMessenger;

use LogicException;
use TelegramOSINT\Auth\AES\AES;
use TelegramOSINT\Auth\AES\PhpSecLibAES;
use TelegramOSINT\Client\AuthKey\AuthKey;
use TelegramOSINT\Exception\MigrateException;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\Logger\ClientDebugLogger;
use TelegramOSINT\Logger\NullLogger;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\MTSerialization\MTDeserializer;
use TelegramOSINT\MTSerialization\OwnImplementation\OwnDeserializer;
use TelegramOSINT\TGConnection\DataCentre;
use TelegramOSINT\TGConnection\Socket\Socket;
use TelegramOSINT\TGConnection\SocketMessenger\EncryptedSocketCallbacks\CallbackMessageListener;
use TelegramOSINT\TGConnection\SocketMessenger\MessengerTools\MessageIdGenerator;
use TelegramOSINT\TGConnection\SocketMessenger\MessengerTools\OuterHeaderWrapper;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\get_config;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\get_state;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\invoke_with_layer;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\msgs_ack;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\updates_get_difference;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\BadServerSalt;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\DcConfigApp;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\MsgContainer;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Rpc\Errors\FloodWait;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Rpc\Errors\MigrateError;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Rpc\RpcError;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Rpc\RpcResult;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Update\UpdatesTooLong;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\UpdatesState;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * Encrypted Telegram-specific socket
 */
class EncryptedSocketMessenger extends TgSocketMessenger
{
    private const NO_CONTENT_RELATED_NAMES = [
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
    /** @var ClientDebugLogger */
    private $logger;
    /** @var AnonymousMessage|null */
    private $config;

    /**
     * @param Socket                 $socket
     * @param AuthKey                $authKey
     * @param MessageListener        $callback
     * @param ClientDebugLogger|null $logger
     *
     * @throws TGException
     */
    public function __construct(
        Socket $socket,
        AuthKey $authKey,
        MessageListener $callback,
        ?ClientDebugLogger $logger = null
    ) {
        parent::__construct($socket);
        $this->messageReceiptCallback = $callback;
        if ($logger === null) {
            $logger = new NullLogger();
        }

        $this->msg_seqno = 0;

        /** @noinspection CryptographicallySecureRandomnessInspection */
        $this->salt = openssl_random_pseudo_bytes(8, $strong);
        if ($this->salt === false || $strong === false) {
            throw new TGException(TGException::ERR_CRYPTO_INVALID);
        }
        /** @noinspection CryptographicallySecureRandomnessInspection */
        $this->sessionId = openssl_random_pseudo_bytes(8, $strong);
        if ($this->sessionId === false || $strong === false) {
            throw new TGException(TGException::ERR_CRYPTO_INVALID);
        }
        $this->authKey = $authKey->getRawAuthKey();
        $this->authKeyId = substr(sha1($this->authKey, true), -8);
        $this->authKeyObj = $authKey;

        $this->aes = new PhpSecLibAES();
        $this->outerHeaderWrapper = new OuterHeaderWrapper();
        $this->msgIdGenerator = new MessageIdGenerator();
        $this->deserializer = new OwnDeserializer();
        $this->logger = $logger;
    }

    /**
     * @throws TGException
     *
     * @return AnonymousMessage|null
     */
    public function readMessage(): ?AnonymousMessage
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
    public function getResponseAsync(TLClientMessage $message, callable $onAsyncResponse): void
    {
        $callback = $onAsyncResponse;
        if ($message instanceof get_config || $message instanceof invoke_with_layer) {
            if ($this->config) {
                $onAsyncResponse($this->config);

                return;
            }
            $callback = function (AnonymousMessage $message) use ($onAsyncResponse) {
                $this->config = $message;
                $onAsyncResponse($message);
            };
        }
        $messageId = $this->msgIdGenerator->generateNext();
        $this->writeIdentifiedMessage($message, $messageId);
        $this->rpcMessages[$messageId] = new CallbackMessageListener($callback);
    }

    /**
     * @param string $payload
     *
     * @throws TGException
     *
     * @return string
     */
    private function decryptPayload(string $payload): string
    {
        $authKeyId = substr($payload, 0, 8);
        $msgKey = substr($payload, 8, 16);
        $payload = substr($payload, 24);

        /** @noinspection TypeUnsafeComparisonInspection */
        if(strcmp($authKeyId, $this->authKeyId) != 0) {
            throw new TGException(TGException::ERR_TL_CONTAINER_BAD_AUTHKEY_ID);
        }
        [$aes_key, $aes_iv] = $this->aes_calculate($msgKey, $this->authKey, false);
        $decryptedPayload = $this->aes->decryptIgeMode($payload, $aes_key, $aes_iv);

        $myMsgKey = substr(hash('sha256', substr($this->authKey, 96, 32).$decryptedPayload, true), 8, 16);
        /** @noinspection TypeUnsafeComparisonInspection */
        if(strcmp($msgKey, $myMsgKey) != 0) {
            throw new TGException(TGException::ERR_TL_CONTAINER_BAD_MSG_KEY);
        }

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
        if(strcmp($session_id, $this->sessionId) !== 0) {
            throw new TGException(TGException::ERR_TL_CONTAINER_BAD_SESSION_ID);
        }
        $msg_id = substr($decryptedPayload, 16, 8);
        $msg_id = unpack('Q', $msg_id)[1];
        $seq_no = substr($decryptedPayload, 24, self::HEADER_LENGTH_BYTES);
        $seq_no = unpack('I', $seq_no);

        if($seq_no % 2 === 1) {
            $this->acknowledgeReceipt($msg_id);
        }

        $message_data_length = unpack('V', substr($decryptedPayload, 28, self::HEADER_LENGTH_BYTES))[1];

        return substr($decryptedPayload, 32, $message_data_length);
    }

    /**
     * @param int $msgId
     *
     * @throws TGException
     */
    private function acknowledgeReceipt($msgId): void
    {
        $this->writeMessage(new msgs_ack([$msgId]));
    }

    private function log(string $code, string $message): void
    {
        $this->logger->debugLibLog($code, $message);
    }

    /**
     * @param string $payload
     *
     * @throws TGException
     *
     * @return AnonymousMessage
     */
    private function deserializePayload(string $payload): AnonymousMessage
    {
        $this->log('Read_Message_Binary', bin2hex($payload));
        $deserializedMessage = $this->deserializer->deserialize($payload);
        $this->log('Read_Message_TL', $deserializedMessage->getDebugPrintable());

        return $deserializedMessage;
    }

    private function reportMessageToSubscriber(): ?AnonymousMessage
    {
        $message = array_shift($this->reportableMessageQueue);
        if($message) {
            $this->messageReceiptCallback->onMessage($message);
        }

        return $message;
    }

    /**
     * @param AnonymousMessage $message
     *
     * @throws TGException
     */
    private function processServiceMessage(AnonymousMessage $message): void
    {
        // rpc
        if(RpcResult::isIt($message)) {
            $rpcResult = new RpcResult($message);
            $msgRequestId = $rpcResult->getRequestMsgId();
            $result = $rpcResult->getResult();

            if(RpcError::isIt($result)) {
                $this->analyzeRpcError(new RpcError($result));
            }

            if (isset($this->rpcMessages[$msgRequestId])) {
                $callback = $this->rpcMessages[$msgRequestId];
                unset($this->rpcMessages[$msgRequestId]);
                $callback->onMessage($result);
            }

            $this->reportableMessageQueue[] = $result;
        }

        // container of messages
        elseif (MsgContainer::isIt($message)){
            // collect messages for further processes: one message process per read
            $this->messagesToBeProcessedQueue = (new MsgContainer($message))->getMessages();
        }

        // salt change
        elseif(BadServerSalt::isIt($message)){
            $badServerSalt = new BadServerSalt($message);
            $this->reSendWithUpdatedSalt($badServerSalt->getNewServerSalt(), (string) $badServerSalt->getBadMsgId());
        }

        elseif(UpdatesTooLong::isIt($message)){
            $this->getResponseAsync(new get_state(), function (AnonymousMessage $response) {
                $updatesState = new UpdatesState($response);
                $this->getResponseAsync(new updates_get_difference(
                    $updatesState->getPts(),
                    $updatesState->getQts(),
                    $updatesState->getDate()
                ), static function (AnonymousMessage $message) {
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
    private function analyzeRpcError(RpcError $rpcError): void
    {
        $parts = explode(':', $this->authKeyObj->getSerializedAuthKey(), 2);
        $userId = $parts[0];

        if($rpcError->isNetworkMigrateError()) {
            $dcId = (new MigrateError($rpcError))->getDcId();

            throw new MigrateException(
                $dcId,
                TGException::ERR_MSG_NETWORK_MIGRATE,
                "reconnection to another DataCenter needed for $userId",
                $this->selectDC($dcId)
            );
        }
        if($rpcError->isPhoneMigrateError()) {
            $dcId = (new MigrateError($rpcError))->getDcId();

            throw new MigrateException(
                $dcId,
                TGException::ERR_MSG_PHONE_MIGRATE,
                "phone $userId already used in another DataCenter: ".$rpcError->getErrorString(),
                $this->selectDC($dcId)
            );
        }
        if($rpcError->isFloodError()) {
            throw new TGException(TGException::ERR_MSG_FLOOD, (new FloodWait($rpcError))->getWaitTimeSec());
        }
        if($rpcError->isUserDeactivated()) {
            throw new TGException(TGException::ERR_MSG_USER_BANNED, "User $userId banned");
        }
        if($rpcError->isAuthKeyUnregistered()) {
            throw new TGException(TGException::ERR_MSG_USER_BANNED, "User $userId unregistered");
        }
        if($rpcError->isPhoneBanned()) {
            throw new TGException(TGException::ERR_MSG_PHONE_BANNED, "User $userId phone banned");
        }
        if($rpcError->isAuthKeyDuplicated()) {
            throw new TGException(TGException::ERR_MSG_BANNED_AUTHKEY_DUPLICATED, "relogin with phone number needed $userId");
        }
        if($rpcError->isSessionRevoked()) {
            throw new TGException(TGException::ERR_MSG_BANNED_SESSION_STOLEN, "bot stolen by revoking session $userId");
        }
    }

    /**
     * @param string $new_salt
     * @param string $badMessageId
     *
     * @throws TGException
     */
    private function reSendWithUpdatedSalt(string $new_salt, string $badMessageId): void
    {
        $this->salt = $new_salt;

        if(!isset($this->sentMessages[$badMessageId])) {
            throw new TGException(TGException::ERR_MSG_RESEND_IMPOSSIBLE);
        }
        $badMessage = $this->sentMessages[$badMessageId];
        $newMessageId = $this->msgIdGenerator->generateNext();

        if(isset($this->rpcMessages[$badMessageId])){
            $this->rpcMessages[$newMessageId] = $this->rpcMessages[$badMessageId];
            unset($this->rpcMessages[$badMessageId]);
        }

        $this->writeIdentifiedMessage($badMessage, $newMessageId);
        unset($this->sentMessages[$badMessageId]);

        /** @noinspection UnusedFunctionResultInspection */
        $this->readMessage();
    }

    /**
     * @param TLClientMessage $payload
     *
     * @throws TGException
     */
    public function writeMessage(TLClientMessage $payload): void
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
    protected function writeIdentifiedMessage(TLClientMessage $payload, $messageId): void
    {
        $this->log('Write_Message_Binary', bin2hex($payload->toBinary()));
        $this->log('Write_Message_ID', (string) $messageId);
        $this->log('Write_Message_TL', $this->deserializer->deserialize($payload->toBinary())->getDebugPrintable());

        $binaryPayload = $this->outerHeaderWrapper->wrap(
            $this->wrapEncryptedContainer(
                $this->wrapEncryptedData($payload, $messageId, $this->isContentRelatedPayload($payload))
            )
        );

        $this->socket->writeBinary($binaryPayload);
        $this->sentMessages[$messageId] = $payload;
    }

    private function isContentRelatedPayload(TLClientMessage $payload): bool
    {
        $nonContentRelatedNames = self::NO_CONTENT_RELATED_NAMES;

        return !in_array($payload->getName(), $nonContentRelatedNames);
    }

    /**
     * @param TLClientMessage $payload
     * @param int             $messageId
     * @param bool            $contentRelated
     *
     * @throws TGException
     *
     * @return string
     */
    private function wrapEncryptedData(TLClientMessage $payload, $messageId, $contentRelated): string
    {
        $seq_no = $this->generate_msg_seqno($contentRelated);

        $payloadStr = $payload->toBinary();
        $length = strlen($payloadStr);

        $padding = $this->calcRemainder(-$length, 16);
        if ($padding < 12) {
            $padding += 16;
        }
        /** @noinspection CryptographicallySecureRandomnessInspection */
        $paddingBytes = openssl_random_pseudo_bytes($padding, $strong);
        if ($paddingBytes === false || $strong === false) {
            throw new TGException(TGException::ERR_CRYPTO_INVALID);
        }

        return
            $this->salt.
            $this->sessionId.
            pack('Q', $messageId).
            pack('VV', $seq_no, $length).
            $payloadStr.
            $paddingBytes;
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
        if ($remainder < 0) {
            $remainder += abs($b);
        }

        return $remainder;
    }

    /**
     * @param string $msg_key
     * @param string $auth_key
     * @param bool   $to_server
     *
     * @return string[]
     */
    private function aes_calculate(string $msg_key, string $auth_key, bool $to_server = true): array
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
    private function wrapEncryptedContainer($payload): string
    {
        $msg_key_large = hash('sha256', substr($this->authKey, 88, 32).$payload, true);
        $msgKey = substr($msg_key_large, 8, 16);

        [$aes_key, $aes_iv] = $this->aes_calculate($msgKey, $this->authKey);
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

    public function getDCInfo(): DataCentre
    {
        return $this->socket->getDCInfo();
    }

    /**
     * @return void
     */
    public function terminate(): void
    {
        $this->socket->terminate();
    }

    /**
     * @param TLClientMessage[] $messages
     * @param callable          $onLastResponse function(AnonymousMessage $message)
     */
    public function getResponseConsecutive(array $messages, callable $onLastResponse): void
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

    /**
     * @param int $dcId
     *
     * @throws TGException
     *
     * @return DataCentre|null
     */
    private function selectDC(int $dcId): ?DataCentre
    {
        $config = $this->getDCConfig();
        if (!$config) {
            return null;
        }
        foreach ($config->getDataCenters() as $dc) {
            if ($dc->getId() === $dcId && $this->isDcAppropriate($dc)) {
                return new DataCentre($dc->getIp(), $dc->getId(), $dc->getPort());
            }
        }

        return null;
    }

    /**
     * @throws TGException
     *
     * @return DcConfigApp|null
     */
    public function getDCConfig(): ?DcConfigApp
    {
        if ($this->config) {
            return new DcConfigApp($this->config);
        }

        return null;
    }
}
