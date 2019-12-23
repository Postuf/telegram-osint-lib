<?php

namespace Auth;

use Client\AuthKey\AuthKey;

interface Authorization
{

    /**
     * @return AuthKey
     */
    public function createAuthKey();

}