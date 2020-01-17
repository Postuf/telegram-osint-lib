<?php

namespace TelegramOSINT\TGConnection\Socket;

use TelegramOSINT\Exception\TGException;
use TelegramOSINT\TGConnection\DataCentre;

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
     * @return string
     */
    public function readBinary(int $length)
    {
        return $this->socket->readBinary($length);
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
