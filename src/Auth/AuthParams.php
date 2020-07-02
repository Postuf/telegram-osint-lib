<?php

namespace TelegramOSINT\Auth;

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
    public function getAuthKey(): string
    {
        return $this->authKey;
    }

    /**
     * @return string binary
     */
    public function getServerSalt(): string
    {
        return $this->serverSalt;
    }
}
