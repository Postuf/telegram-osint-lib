<?php

namespace Client;

use Client\AuthKey\AuthKey;
use Exception\TGException;
use SocksProxyAsync\Proxy;

interface Client
{
    /**
     * @param AuthKey $authKey
     * @param Proxy $proxy
     * @param callable|null $cb
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
