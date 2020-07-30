<?php

namespace TelegramOSINT\Client\InfoObtainingClient\Models;

class UserStatusModel
{
    /**
     * @var bool
     */
    public $is_online;
    /**
     * @var bool
     */
    public $is_hidden;
    /**
     * @var int
     */
    public $was_online;
    /**
     * @var int
     */
    public $expires;
}
