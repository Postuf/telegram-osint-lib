<?php

namespace TGConnection\Socket;

use Exception\TGException;
use LibConfig;
use SocksProxyAsync\Proxy;
use SocksProxyAsync\Socks5Socket;
use SocksProxyAsync\SocksException;
use TGConnection\DataCentre;

class ProxySocket implements Socket
{
    /**
     * Native socket
     *
     * @var resource
     */
    private $socksSocket = null;
    /** @var SocketAsyncTg|Socks5Socket */
    private $socketObject = null;
    /**
     * @var DataCentre
     */
    private $dc;
    /**
     * @var Proxy
     */
    private $proxy;
    /**
     * @var bool
     */
    private $isTerminated = false;
    /** @var callable|null */
    private $cbOnConnected = null;

    /**
     * @param Proxy      $proxy
     * @param DataCentre $dc
     * @param callable|null $cb
     * @throws TGException
     */
    public function __construct(Proxy $proxy, DataCentre $dc, callable $cb = null)
    {
        if(!in_array($proxy->getType(), [Proxy::TYPE_SOCKS5]))
            throw new TGException(TGException::ERR_PROXY_WRONG_PROXY_TYPE);
        $this->dc = $dc;
        $this->proxy = $proxy;

        if ($cb) {
            $this->socketObject = new SocketAsyncTg(
                $this->proxy,
                $this->dc->getDcIp(),
                $this->dc->getDcPort(),
                LibConfig::CONN_SOCKET_PROXY_TIMEOUT_SEC
            );
            $this->cbOnConnected = function() use ($cb) {
                $this->socksSocket = $this->socketObject->getSocksSocket();
                $cb();
            };
            return;
        }

        $this->socketObject = new Socks5Socket($this->proxy, LibConfig::CONN_SOCKET_PROXY_TIMEOUT_SEC);
        try {
            $this->socksSocket = $this->socketObject->createConnected($this->dc->getDcIp(), $this->dc->getDcPort());
            socket_set_nonblock($this->socksSocket);
        } catch (SocksException $e) {
            $this->wrapSocksLibException($e);
        }
    }

    public function runOnConnectedCallback() {
        if ($this->cbOnConnected) {
            $func = $this->cbOnConnected;
            $this->cbOnConnected = null;
            $func();
        }
    }

    public function getSocketObject()
    {
        return $this->socketObject;
    }

    /**
     * @param SocksException $e
     *
     * @throws TGException
     */
    private function wrapSocksLibException(SocksException $e)
    {
        switch ($e->getCode()) {
            case SocksException::UNREACHABLE_PROXY:
                throw new TGException(TGException::ERR_PROXY_UNREACHABLE);
            case SocksException::UNEXPECTED_PROTOCOL_VERSION:
                throw new TGException(TGException::ERR_PROXY_WRONG_SOCKS_VERSION);
            case SocksException::UNSUPPORTED_AUTH_TYPE:
                throw new TGException(TGException::ERR_PROXY_UNKNOWN_AUTH_CODE);
            case SocksException::CONNECTION_NOT_ESTABLISHED:
                throw new TGException(TGException::ERR_PROXY_CONNECTION_NOT_ESTABLISHED);
            case SocksException::AUTH_FAILED:
                throw new TGException(TGException::ERR_PROXY_AUTH_FAILED);
            case SocksException::RESPONSE_WAS_NOT_RECEIVED:
                throw new TGException(TGException::ERR_PROXY_CONNECTION_NOT_ESTABLISHED);
        }
    }

    public function __destruct()
    {
        $this->terminate();
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
        if($this->isTerminated)
            throw new TGException(TGException::ERR_CONNECTION_SOCKET_TERMINATED);

        return @socket_read($this->socksSocket, $length);
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

        return @socket_write($this->socksSocket, $payload);
    }

    /**
     * @return DataCentre
     */
    public function getDCInfo()
    {
        return $this->dc;
    }

    /**
     * @return void
     */
    public function terminate()
    {
        @socket_close($this->socksSocket);
        $this->isTerminated = true;
    }

    /**
     * @throws SocksException
     */
    public function poll(): void
    {
        $socketObject = $this->getSocketObject();
        $this->getSocketObject()->poll();
        if ($socketObject->ready()) {
            $this->runOnConnectedCallback();
        }
    }

    public function ready(): bool
    {
        return $this->getSocketObject()->ready();
    }
}
