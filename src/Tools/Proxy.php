<?php

namespace Tools;

use Exception\TGException;

class Proxy
{
    const TYPE_HTTP = 1;
    const TYPE_SOCKS5 = 3;

    private $server;
    private $port;
    private $type;

    private $password = NULL;
    private $login = NULL;


    /**
     * Proxy constructor.
     * Proxy formats:
     *      1) host:port
     *      2) host:port|login:password
     *
     * @param string $serverAndPort
     * @param int $type
     * @throws TGException
     */
    public function __construct(string $serverAndPort, $type=Proxy::TYPE_SOCKS5)
    {
        if(strstr($serverAndPort, '|')){
            $parts = explode('|', $serverAndPort);
            $serverAndPort = $parts[0];
            $auth = explode(':', $parts[1]);
            if (count($auth) !== 2) {
                throw new TGException(TGException::ERR_PROXY_BAD_FORMAT);
            }
            $this->setLoginPassword($auth[0], $auth[1]);
        }
        $proxyPath = explode(':', $serverAndPort);
        if(sizeof($proxyPath) != 2)
            throw new TGException(TGException::ERR_PROXY_BAD_FORMAT);
        $this->server = trim($proxyPath[0]);
        $this->port = trim($proxyPath[1]);

        $this->type = (int)$type;
    }


    public function setLoginPassword($login, $password)
    {
        $this->login = $login;
        $this->password = $password;
    }


    public function getServer()
    {
        return $this->server;
    }


    public function getPort()
    {
        return $this->port;
    }


    public function getType()
    {
        return $this->type;
    }


    public function isNeedAuth()
    {
        return $this->login && $this->password;
    }


    public function getLogin()
    {
        return $this->login;
    }


    public function getPassword()
    {
        return $this->password;
    }


    function __toString()
    {
        return $this->server.':'.$this->port;
    }


}