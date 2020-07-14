<?php

namespace TelegramOSINT\Client;

use TelegramOSINT\Client\AuthKey\AuthKey;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\Tools\Proxy;

interface Client
{
    /**
     * @param AuthKey       $authKey
     * @param Proxy         $proxy
     * @param callable|null $cb      function()
     *
     * @return void
     */
    public function login(AuthKey $authKey, ?Proxy $proxy, callable $cb): void;

    /**
     * @return void
     */
    public function terminate(): void;

    /**
     * @return bool
     */
    public function isLoggedIn(): bool;

    /**
     * @throws TGException
     *
     * @return bool
     */
    public function pollMessage(): bool;
}
