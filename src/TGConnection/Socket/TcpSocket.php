<?php

namespace TGConnection\Socket;

use Exception\TGException;
use TGConnection\DataCentre;

/**
 * Simple socket over TCP which able to do basic operations
 */
class TcpSocket implements Socket
{
    /**
     * @var DataCentre
     */
    private $dc;
    /**
     * @var resource
     */
    private $socket;
    /**
     * @var bool
     */
    private $isTerminated = false;

    /**
     * @param DataCentre $dc
     *
     * @throws TGException
     */
    public function __construct(DataCentre $dc)
    {
        $this->dc = $dc;

        $this->socket = $this->getSocketResource($dc->getDcIp(), $dc->getDcPort());
        if(!$this->socket)
            throw new TGException(TGException::ERR_CANT_CONNECT);
        stream_set_blocking($this->socket, false);
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
    protected function getSocketResource($ip, $port)
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
     * @return string
     */
    public function readBinary(int $length)
    {
        if($this->isTerminated)
            throw new TGException(TGException::ERR_CONNECTION_SOCKET_TERMINATED);

        return @fread($this->socket, $length);
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
        if($this->isTerminated)
            throw new TGException(TGException::ERR_CONNECTION_SOCKET_TERMINATED);

        return @fwrite($this->socket, $payload);
    }

    /**
     * @return DataCentre
     */
    public function getDCInfo()
    {
        return $this->dc;
    }

    public function terminate()
    {
        @stream_socket_shutdown($this->socket, STREAM_SHUT_RDWR);
        @socket_close($this->socket);
        $this->isTerminated = true;
    }

    public function poll(): void
    {

    }

    public function ready(): bool
    {
        return true;
    }
}
