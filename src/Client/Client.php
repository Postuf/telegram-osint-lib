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
     * @param callable|null $cb           function()
     * @param bool          $updateStatus
     *
     * @return void
     */
    public function login(AuthKey $authKey, Proxy $proxy = null, callable $cb = null, bool $updateStatus = true): void;

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
