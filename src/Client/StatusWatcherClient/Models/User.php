<?php

namespace TelegramOSINT\Client\StatusWatcherClient\Models;

class User
{
    /** @var string|null */
    private $phone;
    /** @var string|null */
    private $username;
    /** @var int|null */
    private $userId;

    public function __construct(?string $phone, ?string $username, ?int $userId = null)
    {
        $this->phone = $phone;
        $this->username = $username;
        $this->userId = $userId;
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

    /**
     * @return int|null
     */
    public function getUserId()
    {
        return $this->userId;
    }
}
