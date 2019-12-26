<?php

namespace Auth;

class AuthParams
{
    /**
     * @var string
     */
    private $authKey;
    /**
     * @var string
     */
    private $serverSalt;

    /**
     * @param string $authKey
     * @param string $serverSalt
     */
    public function __construct($authKey, $serverSalt)
    {
        $this->authKey = $authKey;
        $this->serverSalt = $serverSalt;
    }

    /**
     * @return string binary
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * @return string binary
     */
    public function getServerSalt()
    {
        return $this->serverSalt;
    }
}
