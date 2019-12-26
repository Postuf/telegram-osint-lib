<?php

namespace Client\StatusWatcherClient\Models;

class User
{
    /** @var string|null */
    private $phone;
    /** @var string|null */
    private $username;

    public function __construct(?string $phone, ?string $username)
    {
        $this->phone = $phone;
        $this->username = $username;
    }

    /**
     * @return string|null
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @return string|null
     */
    public function getUsername()
    {
        return $this->username;
    }
}
