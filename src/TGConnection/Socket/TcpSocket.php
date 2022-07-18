<?php

namespace TelegramOSINT\TGConnection\Socket;

use TelegramOSINT\Exception\TGException;
use TelegramOSINT\TGConnection\DataCentre;

/**
 * Simple socket over TCP which able to do basic operations
 */
class TcpSocket implements Socket
{
    private DataCentre $dc;
    /**
     * @var resource
     */
    private $socket;
    /**
     * @var bool
     */
    private bool $isTerminated = false;
    /** @var callable|null */
    private $cb;

    /**
     * @param DataCentre $dc
     * @param callable   $cb
     *
     * @throws TGException
     */
    public function __construct(DataCentre $dc, callable $cb)
    {
        $this->dc = $dc;

        $this->socket = $this->getSocketResource($dc->getDcIp(), $dc->getDcPort());
        if (!$this->socket) {
            throw new TGException(TGException::ERR_CANT_CONNECT);
        }
        stream_set_blocking($this->socket, false);
        $this->cb = $cb;
    }

    public function __destruct()
    {
        $this->terminate();
    }

    /**
     * @param string $ip
     * @param int    $port
     *
     * @return resource
     */
    protected function getSocketResource(string $ip, int $port)
    {
        return @stream_socket_client('tcp://'.$ip.':'.$port, $errno, $errStr);
    }

    /**
     * Persistent read
     *
     * @param int $length
     *
     * @throws TGException
     *
     * @return string|false
     */
    public function readBinary(int $length)
    {
        if ($this->isTerminated) {
            throw new TGException(TGException::ERR_CONNECTION_SOCKET_TERMINATED);
        }

        return @fread($this->socket, $length);
    }

    /**
     * @param string $payload
     *
     * @throws TGException
     *
     * @return int|false
     */
    public function writeBinary(string $payload)
    {
        if ($this->isTerminated) {
            throw new TGException(TGException::ERR_CONNECTION_SOCKET_TERMINATED);
        }

        return @fwrite($this->socket, $payload);
    }

    /**
     * @return DataCentre
     */
    public function getDCInfo(): DataCentre
    {
        return $this->dc;
    }

    public function terminate(): void
    {
        @stream_socket_shutdown($this->socket, STREAM_SHUT_RDWR);
        @socket_close($this->socket);
        $this->isTerminated = true;
    }

    public function poll(): void
    {
        if ($this->cb) {
            $cb = $this->cb;
            $this->cb = null;
            $cb();
        }
    }

    public function ready(): bool
    {
        return !((bool) $this->cb);
    }
}
