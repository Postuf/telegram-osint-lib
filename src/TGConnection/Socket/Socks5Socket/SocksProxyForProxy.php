<?php
namespace TGConnection\Socket\Socks5Socket;

use Tools\Proxy;

class SocksProxyForProxy implements SocksProxy
{
    /**
     * @var Proxy
     */
    private $proxy;

    public function __construct(Proxy $proxy)
    {
        $this->proxy = $proxy;
    }

    public function getServer()
    {
        return $this->proxy->getServer();
    }

    public function getPort()
    {
        return $this->proxy->getPort();
    }

    public function getLogin()
    {
        return $this->proxy->getLogin();
    }

    public function getPassword()
    {
        return $this->proxy->getPassword();
    }

}