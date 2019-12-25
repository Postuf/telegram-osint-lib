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


class BasicClientImpl
    implements BasicClient, MessageListener
{
    private const ONLINE_STATUS_UPDATE_TIME_SEC = 4*60 - 10;

    /**
     * @var SocketMessenger
     */
    private $connection;
    /**
     * @var boolean
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
     * @param AuthKey $authKey
     * @param Proxy|null $proxy
     * @return void
     * @throws TGException
     */
    public function login(AuthKey $authKey, ?Proxy $proxy = null)
    {
        if($this->isLoggedIn())
            throw new TGException(TGException::ERR_CLIENT_ALREADY_LOGGED_IN);

        $dc = $authKey->getAttachedDC();
        $socket = $this->pickSocket($dc, $proxy);

        /** @noinspection PhpParamsInspection */
        $this->connection = new EncryptedSocketMessenger($socket, $authKey, $this);
        $this->isLoggedIn = true;
    }


    /**
     * @return boolean
     */
    public function isLoggedIn()
    {
        return $this->isLoggedIn;
    }


    /**
     * @return void
     * @throws TGException
     */
    public function throwIfNotLoggedIn()
    {
        if(!$this->isLoggedIn())
            throw new TGException(TGException::ERR_CLIENT_NOT_LOGGED_IN);
    }


    /**
     * @param DataCentre $dc
     * @param Proxy|null $proxy
     * @return Socket
     * @throws TGException
     */
    private function pickSocket(DataCentre $dc, Proxy $proxy = null)
    {
        if($proxy instanceof Proxy){
            if($proxy->getType() == Proxy::TYPE_SOCKS5)
                return new ProxySocket($proxy, $dc);
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
     * return boolean
     * @throws TGException
     */
    public function pollMessage()
    {
        $this->checkConnectionAlive();
        $this->pingIfNeeded();
        $this->setOnlineStatusIfExpired();
        return $this->getConnection()->readMessage() != null;
    }


    /**
     *
     * @param AnonymousMessage $message
     */
    public function onMessage(AnonymousMessage $message)
    {
        $this->lastIncomingMessageReceiptTime = time();
        if($this->messageHandler)
            $this->messageHandler->onMessage($message);
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
            throw new TGException(TGException::ERR_CONNECTION_SHUTDOWN);
    }


    /**
     * @throws TGException
     */
    private function pingIfNeeded()
    {
        $elapsedSinceLastPing = time() - $this->lastPingTime;
        if($elapsedSinceLastPing >= LibConfig::CONN_PING_INTERVAL_SEC){

            if(ping_delay_disconnect::getDisconnectTimeoutSec() <= LibConfig::CONN_PING_INTERVAL_SEC)
                throw new TGException(TGException::ERR_CONNECTION_BAD_PING_COMBINATION, 'delay < ping');

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
     * @return void
     * @throws TGException
     */
    public function setMessageListener(MessageListener $messageCallback)
    {
        if($this->messageHandler)
            throw new TGException(TGException::ERR_ASSERT_LISTENER_ALREADY_SET);

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