<?php

namespace TelegramOSINT\Auth;

interface Authorization
{
    /**
     * @param callable $onAuthKeyReady function(AuthKey $authKey)
     */
    public function createAuthKey(callable $onAuthKeyReady);
}
