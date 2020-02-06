<?php

declare(strict_types=1);

namespace TelegramOSINT\TGConnection\Socket;

use SocksProxyAsync\SocksException;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\LibConfig;
use TelegramOSINT\TGConnection\DataCentre;
use TelegramOSINT\Tools\Proxy;

class NonBlockingProxySocket extends ProxySocket
{
    /** @var SocketAsyncTg|null */
    private $socketObjectAsync = null;

    /**
     * @param Proxy      $proxy
     * @param DataCentre $dc
     * @param callable   $onSocketReady function()
     * @param int        $timeout
     *
     * @throws TGException
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct(
        Proxy $proxy,
        DataCentre $dc,
        callable $onSocketReady,
        int $timeout = LibConfig::CONN_SOCKET_PROXY_TIMEOUT_SEC
    ) {
        if(!in_array($proxy->getType(), [Proxy::TYPE_SOCKS5]))
            throw new TGException(TGException::ERR_PROXY_WRONG_PROXY_TYPE);
        $this->dc = $dc;
        $this->proxy = $proxy;

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

    }

    private function runOnConnectedCallback() {
        if ($this->cbOnConnected) {
            $func = $this->cbOnConnected;
            $this->cbOnConnected = null;
            $func();
        }
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
            throw new TGException(TGException::ERR_PROXY_LONG_STEP, $e->getMessage());
        }
        if ($this->socketObjectAsync->ready()) {
            $this->runOnConnectedCallback();
        }
    }

    public function ready(): bool
    {
        return $this->socketObjectAsync->ready();
    }

    public function __destruct()
    {
        $this->socketObjectAsync->stop();
        parent::__destruct();
    }
}
