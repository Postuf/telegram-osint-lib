<?php

namespace TelegramOSINT\Tools;

class Username
{
    public static function equal(?string $username1, ?string $username2)
    {
        return strcasecmp(trim($username1), trim($username2)) == 0;
    }
}
