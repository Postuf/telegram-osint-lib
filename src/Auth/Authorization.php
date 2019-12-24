<?php

namespace Auth;

use Client\AuthKey\AuthKey;

interface Authorization
{

    /**
     * @param callable $cb function(AuthKey $authKey)
     */
    public function createAuthKey(callable $cb);

}