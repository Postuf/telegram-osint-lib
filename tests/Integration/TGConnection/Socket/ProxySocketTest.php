<?php

declare(strict_types=1);

namespace Integration\TGConnection\Socket;

use Exception\TGException;
use PHPUnit\Framework\TestCase;
use SocksProxyAsync\Proxy;
use SocksProxyAsync\SocksException;
use TGConnection\DataCentre;
use TGConnection\Socket\ProxySocket;

class ProxySocketTest extends TestCase
{
    /**
     * @throws TGException
     * @throws SocksException
     */
    public function test_proxy_connect(): void
    {
        $socket = new ProxySocket(new Proxy('127.0.0.1:1080'), DataCentre::getDefault());
        $this->assertEquals(true, $socket->ready());
        $socket->terminate();
    }
}
