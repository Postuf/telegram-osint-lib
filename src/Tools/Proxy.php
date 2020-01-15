<?php

declare(strict_types=1);

namespace Tools;

use Exception\TGException;
use SocksProxyAsync\Proxy as LibProxy;
use SocksProxyAsync\SocksException;

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
