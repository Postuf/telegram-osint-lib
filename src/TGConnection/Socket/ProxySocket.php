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
    protected $socksSocket;
    /**
     * @var DataCentre
     */
    protected $dc;
    /**
     * @var Proxy
     */
    protected $proxy;
    /**
     * @var bool
     */
    private $isTerminated = false;
    /** @var callable|null */
    protected $cbOnConnected;
    /** @var Socks5Socket */
    private $socketObject;

    /**
     * @param Proxy      $proxy
     * @param DataCentre $dc
     * @param int        $timeout
     *
     * @throws TGException
     */
    public function __construct(
        Proxy $proxy,
        DataCentre $dc,
        int $timeout = LibConfig::CONN_SOCKET_PROXY_TIMEOUT_SEC
    ) {
        if($proxy->getType() !== Proxy::TYPE_SOCKS5) {
            throw new TGException(TGException::ERR_PROXY_WRONG_PROXY_TYPE);
        }
        $this->dc = $dc;
        $this->proxy = $proxy;

        /** @noinspection UnusedConstructorDependenciesInspection */
        $this->socketObject = new Socks5Socket($this->proxy, $timeout);

        try {
            $this->socksSocket = $this->socketObject->createConnected($this->dc->getDcIp(), $this->dc->getDcPort());
            socket_set_nonblock($this->socksSocket);
        } catch (SocksException $e) {
            $this->wrapSocksLibException($e);
        }
    }

    /**
     * @param SocksException $e
     *
     * @throws TGException
     */
    private function wrapSocksLibException(SocksException $e): void
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
     * @return string|false
     */
    public function readBinary(int $length)
    {
        if($this->isTerminated) {
            throw new TGException(TGException::ERR_CONNECTION_SOCKET_TERMINATED);
        }

        return @socket_read($this->socksSocket, $length);
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
        if($this->isTerminated) {
            throw new TGException(TGException::ERR_CONNECTION_SOCKET_TERMINATED);
        }

        return @socket_write($this->socksSocket, $payload);
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
        if (is_resource($this->socksSocket)) {
            @socket_shutdown($this->socksSocket, 2);
            @socket_close($this->socksSocket);
        }
        $this->socksSocket = null;
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
