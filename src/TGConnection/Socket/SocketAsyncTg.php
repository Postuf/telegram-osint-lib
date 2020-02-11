<?php

declare(strict_types=1);

namespace TelegramOSINT\TGConnection\Socket;

use SocksProxyAsync\SocketAsync;

/**
 * @internal
 */
class SocketAsyncTg extends SocketAsync
{
    /**
     * @return resource
     */
    public function getSocksSocket() {
        return $this->socksSocket;
    }
}
