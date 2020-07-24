<?php

namespace TelegramOSINT\Client\InfoObtainingClient\Models;

class UserStatusModel
{
    public bool $is_online;
    /**
     * @var bool
     */
    public bool $is_hidden = false;
    public ?int $was_online = null;
    public ?int $expires = null;
}
