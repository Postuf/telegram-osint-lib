<?php

declare(strict_types=1);

namespace TelegramOSINT\Tools;

use SocksProxyAsync\Proxy as LibProxy;
use SocksProxyAsync\SocksException;
use TelegramOSINT\Exception\TGException;

class Proxy extends LibProxy
{
    /**
     * @param string $serverAndPort
     * @param int    $type
     *
     * @throws TGException
     */
    public function __construct(string $serverAndPort, int $type = LibProxy::TYPE_SOCKS5)
    {
        try {
            LibProxy::__construct($serverAndPort, $type);
        } catch (SocksException $e) {
            throw new TGException(0, $e->getMessage());
        }
    }
}
