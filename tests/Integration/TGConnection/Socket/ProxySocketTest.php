<?php

declare(strict_types=1);

namespace Integration\TGConnection\Socket;

use PHPUnit\Framework\TestCase;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\TGConnection\DataCentre;
use TelegramOSINT\TGConnection\Socket\ProxySocket;
use TelegramOSINT\Tools\Proxy;

class ProxySocketTest extends TestCase
{
    /**
     * @throws TGException
     */
    public function test_proxy_connect(): void
    {
        $socket = new ProxySocket(new Proxy('127.0.0.1:1080'), DataCentre::getDefault());
        $this->assertEquals(true, $socket->ready());
        $socket->terminate();
    }

    /**
     * @throws TGException
     */
    public function test_proxy_connect_async(): void
    {
        $isReady = false;
        $socket = new ProxySocket(
            new Proxy('127.0.0.1:1080'),
            DataCentre::getDefault(),
            function () use (&$isReady) {
                $isReady = true;
            }
        );
        $timeLimit = 5000; // 5 sec
        for ($i = 0; $i < $timeLimit; $i++) {
            if (!$socket->ready()) {
                $socket->poll();
            }
            usleep(1000);
        }
        $this->assertEquals(true, $socket->ready());
        $this->assertEquals(true, $isReady);
        $socket->terminate();
    }
}
