<?php

namespace TelegramOSINT\TGConnection\Socket;

use SocksProxyAsync\Socks5Socket;
use SocksProxyAsync\SocksException;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\LibConfig;
use TelegramOSINT\TGConnection\DataCentre;
use TelegramOSINT\Tools\Proxy;

class ProxySocket implements Socket
{
    /**
     * Native socket
     *
     * @var resource
     */
    private $socksSocket = null;
    /** @var Socks5Socket|null */
    private $socketObject = null;
    /** @var SocketAsyncTg|null */
    private $socketObjectAsync = null;
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
     * @param Proxy         $proxy
     * @param DataCentre    $dc
     * @param callable|null $onSocketReady function()
     * @param int           $timeout
     *
     * @throws TGException
     */
    public function __construct(
        Proxy $proxy,
        DataCentre $dc,
        callable $onSocketReady = null,
        int $timeout = LibConfig::CONN_SOCKET_PROXY_TIMEOUT_SEC
    ) {
        if(!in_array($proxy->getType(), [Proxy::TYPE_SOCKS5]))
            throw new TGException(TGException::ERR_PROXY_WRONG_PROXY_TYPE);
        $this->dc = $dc;
        $this->proxy = $proxy;

        if ($onSocketReady) {
            $this->socketObjectAsync = new SocketAsyncTg(
                $this->proxy,
                $this->dc->getDcIp(),
                $this->dc->getDcPort(),
                $timeout
            );
            $this->cbOnConnected = function () use ($onSocketReady) {
                $this->socksSocket = $this->socketObjectAsync->getSocksSocket();
                $onSocketReady();
            };

            return;
        }

        $this->socketObject = new Socks5Socket($this->proxy, $timeout);

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
     * @throws TGException
     */
    public function poll(): void
    {
        if (!$this->socketObjectAsync) {
            return;
        }

        try {
            $this->socketObjectAsync->poll();
        } catch (SocksException $e) {
            throw new TGException(TGException::ERR_SOCKS_STEP_ERROR, $e->getMessage());
        }
        if ($this->socketObjectAsync->ready()) {
            $this->runOnConnectedCallback();
        }
    }

    public function ready(): bool
    {
        return $this->socketObject
            ? true
            : $this->socketObjectAsync->ready();
    }
}
