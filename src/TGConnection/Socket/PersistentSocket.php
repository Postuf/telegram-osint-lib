<?php

namespace TGConnection\Socket;

use Exception\TGException;
use LibConfig;
use TGConnection\DataCentre;

/**
 * Wrapper over @Socket which provides IO-blocking methods of reading and writing
 * expected bytes amount
 */
class PersistentSocket implements Socket
{
    /**
     * @var Socket
     */
    private $socket;

    public function __construct(Socket $socket)
    {
        $this->socket = $socket;
    }

    /**
     * @param int $length
     *
     * @throws TGException
     *
     * @return string
     */
    public function readBinary(int $length)
    {
        $read = '';
        $readStartTime = $this->getMicroTime();

        while(strlen($read) < $length) {
            $read .= $this->socket->readBinary($length - strlen($read));
            $elapsedTime = $this->getMicroTime() - $readStartTime;
            if($elapsedTime > LibConfig::CONN_SOCKET_TIMEOUT_PERSISTENT_READ_MS)
                break;
            usleep(LibConfig::CONN_SOCKET_RESPONSE_DELAY_MICROS);
        }

        if(strlen($read) != $length)
            throw new TGException(TGException::ERR_CONNECTION_SOCKET_READ_TIMEOUT);

        return $read;
    }

    private function getMicroTime()
    {
        return round(microtime(true) * 1000);
    }

    /**
     * @param string $payload
     *
     * @throws TGException
     *
     * @return int
     */
    public function writeBinary(string $payload)
    {
        $payloadSize = strlen($payload);
        if($payloadSize == 0)
            throw new TGException(TGException::ERR_CONNECTION_EMPTY_BUFFER_WRITE);
        $written = $this->socket->writeBinary($payload);

        if($written != $payloadSize)
            throw new TGException(TGException::ERR_CONNECTION_SOCKET_CLOSED);

        return $written;
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

    public function poll(): void
    {

    }

    public function ready(): bool
    {
        return true;
    }
}
