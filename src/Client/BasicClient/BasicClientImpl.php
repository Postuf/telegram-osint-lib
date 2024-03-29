<?php

namespace TelegramOSINT\Client\BasicClient;

use TelegramOSINT\Client\AuthKey\AuthKey;
use TelegramOSINT\Client\AuthKey\AuthorizedAuthKey;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\LibConfig;
use TelegramOSINT\Logger\ClientDebugLogger;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\Registration\AccountInfo;
use TelegramOSINT\TGConnection\DataCentre;
use TelegramOSINT\TGConnection\Socket\NonBlockingProxySocket;
use TelegramOSINT\TGConnection\Socket\Socket;
use TelegramOSINT\TGConnection\Socket\TcpSocket;
use TelegramOSINT\TGConnection\SocketMessenger\EncryptedSocketMessenger;
use TelegramOSINT\TGConnection\SocketMessenger\MessageListener;
use TelegramOSINT\TGConnection\SocketMessenger\SocketMessenger;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\get_config;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\init_connection;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\invoke_with_layer;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\ping_delay_disconnect;
use TelegramOSINT\Tools\Proxy;

class BasicClientImpl implements BasicClient, MessageListener
{
    /**
     * @var SocketMessenger
     */
    private ?SocketMessenger $connection = null;
    /**
     * @var bool
     */
    private bool $isLoggedIn = false;
    /**
     * @var int
     */
    private int $lastPingTime = 0;
    /**
     * @var int
     */
    private $lastIncomingMessageReceiptTime;
    /**
     * @var MessageListener
     */
    private ?MessageListener $messageHandler = null;
    /** @var AuthKey|null */
    private ?AuthKey $authKey;
    /** @var Socket|null */
    private ?Socket $socket;
    /** @var int seconds */
    private int $proxyTimeout;
    /** @var ClientDebugLogger|null */
    private ?ClientDebugLogger $logger;

    public function __construct(
        int $proxyTimeout = LibConfig::CONN_SOCKET_PROXY_TIMEOUT_SEC,
        ?ClientDebugLogger $logger = null
    ) {
        $this->lastIncomingMessageReceiptTime = time();
        $this->proxyTimeout = $proxyTimeout;
        $this->logger = $logger;
    }

    /**
     * @throws TGException
     * @noinspection PhpRedundantCatchClauseInspection
     */
    public function __destruct()
    {
        try {
            $this->terminate();
        } catch (TGException $e) {
            if ($e->getCode() !== TGException::ERR_CONNECTION_SOCKET_TERMINATED) {
                throw $e;
            }
        }
    }

    /**
     * @throws TGException
     *
     * @return SocketMessenger
     */
    protected function getSocketMessenger(): SocketMessenger
    {
        return new EncryptedSocketMessenger(
            $this->socket,
            $this->authKey,
            $this,
            $this->logger
        );
    }

    final protected function getAuthKey(): ?AuthKey
    {
        return $this->authKey;
    }

    /**
     * @param AuthKey       $authKey
     * @param Proxy|null    $proxy
     * @param callable|null $cb      function()
     *
     * @throws TGException
     *
     * @return void
     */
    public function login(AuthKey $authKey, ?Proxy $proxy, callable $cb): void
    {
        if ($this->isLoggedIn()) {
            throw new TGException(TGException::ERR_CLIENT_ALREADY_LOGGED_IN, $this->getUserId());
        }
        $dc = $authKey->getAttachedDC();
        $postSocket = function () use ($authKey) {
            $this->authKey = $authKey;
            $this->connection = $this->getSocketMessenger();
            $this->isLoggedIn = true;

            $this->bumpProtocolVersion();
        };
        $this->socket = $this->pickSocket($dc, $proxy, static function () use ($cb, $postSocket) {
            $postSocket();
            $cb();
        });
    }

    private function bumpProtocolVersion(): void
    {
        $accountInfo = $this->authKey instanceof AuthorizedAuthKey ?
            AccountInfo::generateFromAuthKey($this->authKey) :
            AccountInfo::generate();

        $initConnection = new init_connection($accountInfo, new get_config());
        $requestWithLayer = new invoke_with_layer(LibConfig::APP_DEFAULT_TL_LAYER_VERSION, $initConnection);
        /** @noinspection NullPointerExceptionInspection */
        $this->getConnection()->getResponseAsync($requestWithLayer, static function (AnonymousMessage $response) {});
    }

    /**
     * @return bool
     */
    public function isLoggedIn(): bool
    {
        return $this->isLoggedIn;
    }

    /**
     * @param string $message
     *
     * @throws TGException
     *
     * @return void
     */
    public function throwIfNotLoggedIn(string $message = ''): void
    {
        if (!$this->isLoggedIn()) {
            throw new TGException(TGException::ERR_CLIENT_NOT_LOGGED_IN, $message);
        }
    }

    /**
     * @param DataCentre    $dc
     * @param Proxy|null    $proxy
     * @param callable|null $cb    function()
     *
     * @throws TGException
     *
     * @return Socket
     */
    protected function pickSocket(DataCentre $dc, ?Proxy $proxy, callable $cb): Socket
    {
        if ($proxy !== null && $proxy->getType() === Proxy::TYPE_SOCKS5) {
            return new NonBlockingProxySocket($proxy, $dc, $cb, $this->proxyTimeout);
        }

        return new TcpSocket($dc, $cb);
    }

    /**
     * @return SocketMessenger
     */
    public function getConnection(): ?SocketMessenger
    {
        return $this->connection;
    }

    /**
     * @throws TGException
     *
     * @return AnonymousMessage|null
     */
    protected function prePollMessage(): ?AnonymousMessage
    {
        if (!$this->socket->ready()) {
            $this->socket->poll();

            return null;
        }
        $this->checkConnectionAlive();
        if (!$this->connection) {
            return null;
        }
        $this->pingIfNeeded();

        /** @noinspection NullPointerExceptionInspection */
        return $this->getConnection()->readMessage();
    }

    /**
     * @throws TGException
     *
     * @return bool
     */
    public function pollMessage(): bool
    {
        $readMessage = $this->prePollMessage();

        return $readMessage !== null;
    }

    /**
     * @param AnonymousMessage $message
     */
    public function onMessage(AnonymousMessage $message): void
    {
        $this->lastIncomingMessageReceiptTime = time();
        if ($this->messageHandler) {
            $this->messageHandler->onMessage($message);
        }
    }

    private function getUserId()
    {
        if ($this->authKey) {
            $parts = explode(':', $this->authKey->getSerializedAuthKey(), 2);

            return $parts[0];
        }

        return '';
    }

    /**
     * @throws TGException
     */
    private function checkConnectionAlive(): void
    {
        if ($this->lastIncomingMessageReceiptTime === 0) {
            $this->lastIncomingMessageReceiptTime = time();
        }

        $elapsedSinceLastMessage = time() - $this->lastIncomingMessageReceiptTime;
        $allowedIdleTimeSec = 5;

        if ($elapsedSinceLastMessage >= LibConfig::CONN_PING_INTERVAL_SEC + $allowedIdleTimeSec) {
            throw new TGException(TGException::ERR_CONNECTION_SHUTDOWN, $this->getUserId());
        }
    }

    /**
     * @throws TGException
     */
    private function pingIfNeeded(): void
    {
        $elapsedSinceLastPing = time() - $this->lastPingTime;
        if ($elapsedSinceLastPing >= LibConfig::CONN_PING_INTERVAL_SEC) {
            if (ping_delay_disconnect::getDisconnectTimeoutSec() <= LibConfig::CONN_PING_INTERVAL_SEC) {
                throw new TGException(TGException::ERR_CONNECTION_BAD_PING_COMBINATION, 'delay < ping for '.$this->getUserId());
            }
            /** @noinspection NullPointerExceptionInspection */
            $this->getConnection()->writeMessage(new ping_delay_disconnect());
            $this->lastPingTime = time();
        }
    }

    /**
     * @param MessageListener $messageCallback
     *
     * @throws TGException
     *
     * @return void
     */
    public function setMessageListener(MessageListener $messageCallback): void
    {
        if ($this->messageHandler) {
            throw new TGException(TGException::ERR_ASSERT_LISTENER_ALREADY_SET, $this->getUserId());
        }
        $this->messageHandler = $messageCallback;
    }

    public function terminate(): void
    {
        if ($this->getConnection()) {
            $this->getConnection()->terminate();
        }
    }
}
