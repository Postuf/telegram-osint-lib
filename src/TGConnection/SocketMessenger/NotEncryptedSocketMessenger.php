<?php

namespace TGConnection\SocketMessenger;


use Exception\TGException;
use LibConfig;
use Logger\Logger;
use MTSerialization\AnonymousMessage;
use MTSerialization\MTDeserializer;
use MTSerialization\OwnImplementation\OwnDeserializer;
use TGConnection\DataCentre;
use TGConnection\Socket\PersistentSocket;
use TGConnection\Socket\Socket;
use TGConnection\SocketMessenger\MessengerTools\MessageIdGenerator;
use TGConnection\SocketMessenger\MessengerTools\OuterHeaderWrapper;
use TLMessage\TLMessage\TLClientMessage;

class NotEncryptedSocketMessenger implements SocketMessenger
{

    /**
     * @var Socket
     */
    private $socket;
    /**
     * @var PersistentSocket
     */
    private $persistentSocket;
    /**
     * @var OuterHeaderWrapper
     */
    private $outerHeaderWrapper;
    /**
     * @var MessageIdGenerator
     */
    private $msgIdGenerator;
    /**
     * @var MTDeserializer
     */
    private $deserializer;


    /**
     * @param Socket $socket
     */
    public function __construct(Socket $socket)
    {
        $this->socket = $socket;
        $this->persistentSocket = new PersistentSocket($this->socket);
        $this->outerHeaderWrapper = new OuterHeaderWrapper();
        $this->msgIdGenerator = new MessageIdGenerator();
        $this->deserializer = new OwnDeserializer();
    }


    /**
     * @return AnonymousMessage
     * @throws TGException
     */
    public function readMessage()
    {
        // header
        $lengthValue = $this->socket->readBinary(4);
        $readLength = strlen($lengthValue);
        if($readLength == 0)
            return null;
        if($readLength != 4)
            throw new TGException(TGException::ERR_DESERIALIZER_BROKEN_BINARY_READ, '4!='.$readLength);

        // data
        $payloadLength = unpack('I', $lengthValue)[1] - 4;
        $payload = $this->persistentSocket->readBinary($payloadLength);

        // full TL packet
        $packet = $lengthValue.$payload;

        Logger::log('Read_Message_Binary', bin2hex($packet));

        $decoded = $this->decodePayload($this->outerHeaderWrapper->unwrap($packet));
        $deserialized = $this->deserializer->deserialize($decoded);

        Logger::log('Read_Message_Binary', bin2hex($decoded));
        Logger::log('Read_Message_TL', $deserialized->getDebugPrintable());

        return $deserialized;
    }


    /**
     * @param string $payload
     * @return false|string
     * @throws TGException
     */
    private function decodePayload($payload)
    {
        $auth_key_id = unpack('V', substr($payload, 0, 8))[1];

        // must be 0 because it is unencrypted messaging
        if($auth_key_id != 0)
            throw new TGException(TGException::ERR_TL_CONTAINER_BAD_AUTHKEY_ID_MUST_BE_0);

        $message_data_length = unpack('V', substr($payload, 16, 4))[1];
        return substr($payload, 20, $message_data_length);
    }


    /**
     * @param TLClientMessage $payload
     * @throws TGException
     */
    public function writeMessage(TLClientMessage $payload)
    {
        $payloadStr = $this->outerHeaderWrapper->wrap(
            $this->wrapPayloadWithMessageId($payload->toBinary())
        );

        $this->socket->writeBinary($payloadStr);

        Logger::log('Write_Message_Binary', bin2hex($payload->toBinary()));
        Logger::log('Write_Message_TL', $this->deserializer->deserialize($payload->toBinary())->getDebugPrintable());
    }


    /**
     * @param string $payload
     * @return string
     */
    private function wrapPayloadWithMessageId(string $payload)
    {
        $msg_id = $this->msgIdGenerator->generateNext();
        $length = strlen($payload);
        $payload = pack('x8PI', $msg_id, $length) . $payload;
        return $payload;
    }


    /**
     * @param TLClientMessage $message
     * @param int $timeoutMs
     * @return AnonymousMessage
     * @throws TGException
     */
    public function getResponse(TLClientMessage $message, $timeoutMs = LibConfig::CONN_SOCKET_TIMEOUT_WAIT_RESPONSE_MS)
    {
        $this->writeMessage($message);
        $startTimeMs = microtime(true) * 1000;

        while(true){
            $response = $this->readMessage();
            if($response)
                return $response;

            $currentTimeMs = microtime(true) * 1000;
            if(($currentTimeMs - $startTimeMs) > $timeoutMs)
                break;

            usleep(LibConfig::CONN_SOCKET_RESPONSE_DELAY_MICROS);
        }

        throw new TGException(TGException::ERR_MSG_RESPONSE_TIMEOUT);
    }


    /**
     * @return DataCentre
     */
    public function getDCInfo()
    {
        return $this->socket->getDCInfo();
    }


    public function terminate()
    {
        $this->socket->terminate();
    }


    public function getResponseAsync(TLClientMessage $message, callable $onAsyncResponse)
    {

    }
}