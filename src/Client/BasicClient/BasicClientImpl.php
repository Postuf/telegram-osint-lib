<?php

namespace Client\BasicClient;

use Client\AuthKey\AuthKey;
use Exception\TGException;
use LibConfig;
use MTSerialization\AnonymousMessage;
use SocksProxyAsync\Proxy;
use TGConnection\DataCentre;
use TGConnection\Socket\ProxySocket;
use TGConnection\Socket\Socket;
use TGConnection\Socket\TcpSocket;
use TGConnection\SocketMessenger\EncryptedSocketMessenger;
use TGConnection\SocketMessenger\MessageListener;
use TGConnection\SocketMessenger\SocketMessenger;
use TLMessage\TLMessage\ClientMessages\Shared\update_status;
use TLMessage\TLMessage\ClientMessages\TgApp\ping_delay_disconnect;

class BasicClientImpl implements BasicClient, MessageListener
{
    private const ONLINE_STATUS_UPDATE_TIME_SEC = 4 * 60 - 10;

    /**
     * @var SocketMessenger
     */
    private $connection;
    /**
     * @var bool
     */
    private $isLoggedIn;
    /**
     * @var int
     */
    private $lastPingTime;
    /**
     * @var int
     */
    private $lastIncomingMessageReceiptTime;
    /**
     * @var int
     */
    private $lastStatusOnlineSet;
    /**
     * @var MessageListener
     */
    private $messageHandler;
    /** @var AuthKey|null */
    private $authKey;
    /** @var Socket|null */
    private $socket;

    public function __construct()
    {
        $this->lastPingTime = 0;
        $this->lastIncomingMessageReceiptTime = time();
        $this->lastStatusOnlineSet = 0;
        $this->isLoggedIn = false;
    }

    /**
     * @throws TGException
     */
    public function __destruct()
    {
        try {
            $this->terminate();
        } catch (TGException $e){
            if($e->getCode() != TGException::ERR_CONNECTION_SOCKET_TERMINATED)
                throw $e;
        }
    }

    /**
     * @param AuthKey       $authKey
     * @param Proxy|null    $proxy
     * @param callable|null $cb      function()
     *
     * @throws TGException
     *
     * @return void
     * @return void
     */
    public function login(AuthKey $authKey, ?Proxy $proxy = null, callable $cb = null)
    {
        if($this->isLoggedIn())
            throw new TGException(TGException::ERR_CLIENT_ALREADY_LOGGED_IN, $this->getUserId());
        $dc = $authKey->getAttachedDC();
        $this->socket = $this->pickSocket($dc, $proxy, $cb);

        $this->connection = new EncryptedSocketMessenger($this->socket, $authKey, $this);
        $this->authKey = $authKey;
        $this->isLoggedIn = true;
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->isLoggedIn;
    }

    /**
     * @throws TGException
     *
     * @return void
     */
    public function throwIfNotLoggedIn()
    {
        if(!$this->isLoggedIn())
            throw new TGException(TGException::ERR_CLIENT_NOT_LOGGED_IN);
    }

    /**
     * @param DataCentre    $dc
     * @param Proxy|null    $proxy
     * @param callable|null $cb    function()
     *
     * @throws TGException
     *
     * @return Socket
     * @return Socket
     */
    private function pickSocket(DataCentre $dc, Proxy $proxy = null, callable $cb = null)
    {
        if($proxy instanceof Proxy){
            if($proxy->getType() == Proxy::TYPE_SOCKS5)
                return new ProxySocket($proxy, $dc, $cb);
        }

        return new TcpSocket($dc);
    }

    /**
     * @return SocketMessenger
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @throws TGException
     *
     * @return bool
     */
    public function pollMessage()
    {
        if (!$this->socket->ready()) {
            $this->socket->poll();

            return false;
        }
        $this->checkConnectionAlive();
        $this->pingIfNeeded();
        $this->setOnlineStatusIfExpired();

        return $this->getConnection()->readMessage() != null;
    }

    /**
     * @param AnonymousMessage $message
     */
    public function onMessage(AnonymousMessage $message)
    {
        $this->lastIncomingMessageReceiptTime = time();
        if($this->messageHandler)
            $this->messageHandler->onMessage($message);
    }

    private function getUserId() {
        if (!$this->authKey) {
            $parts = explode(':', $this->authKey->getSerializedAuthKey());

            return $parts[0];
        }

        return '';
    }

    /**
     * @throws TGException
     */
    private function checkConnectionAlive()
    {
        if($this->lastIncomingMessageReceiptTime == 0)
            $this->lastIncomingMessageReceiptTime = time();

        $elapsedSinceLastMessage = time() - $this->lastIncomingMessageReceiptTime;
        $allowedIdleTimeSec = 5;

        if($elapsedSinceLastMessage >= LibConfig::CONN_PING_INTERVAL_SEC + $allowedIdleTimeSec)
            throw new TGException(TGException::ERR_CONNECTION_SHUTDOWN, $this->getUserId());
    }

    /**
     * @throws TGException
     */
    private function pingIfNeeded()
    {
        $elapsedSinceLastPing = time() - $this->lastPingTime;
        if($elapsedSinceLastPing >= LibConfig::CONN_PING_INTERVAL_SEC){

            if(ping_delay_disconnect::getDisconnectTimeoutSec() <= LibConfig::CONN_PING_INTERVAL_SEC)
                throw new TGException(TGException::ERR_CONNECTION_BAD_PING_COMBINATION, 'delay < ping for '.$this->getUserId());
            $this->getConnection()->writeMessage(new ping_delay_disconnect());
            $this->lastPingTime = time();
        }
    }

    private function setOnlineStatusIfExpired()
    {
        $elapsedTimeSinceLastUpdate = time() - $this->lastStatusOnlineSet;
        if($elapsedTimeSinceLastUpdate >= self::ONLINE_STATUS_UPDATE_TIME_SEC){
            $this->getConnection()->writeMessage(new update_status(true));
            $this->lastStatusOnlineSet = time();
        }
    }

    /**
     * @param MessageListener $messageCallback
     *
     * @throws TGException
     *
     * @return void
     */
    public function setMessageListener(MessageListener $messageCallback)
    {
        if($this->messageHandler)
            throw new TGException(TGException::ERR_ASSERT_LISTENER_ALREADY_SET, $this->getUserId());
        $this->messageHandler = $messageCallback;
    }

    public function terminate()
    {
        if($this->getConnection()) {
            $this->getConnection()->writeMessage(new update_status(false));
            $this->getConnection()->terminate();
        }
    }
}
