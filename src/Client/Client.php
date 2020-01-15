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
    public function login(AuthKey $authKey, Proxy $proxy = null, callable $cb = null);

    /**
     * @return void
     */
    public function terminate();

    /**
     * @return bool
     */
    public function isLoggedIn();

    /**
     * @throws TGException
     *
     * @return bool
     */
    public function pollMessage();
}
