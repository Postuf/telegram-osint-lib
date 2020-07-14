<?php

declare(strict_types=1);

namespace TelegramOSINT\Tools;

class Username
{
    public static function equal(?string $username1, ?string $username2): bool
    {
        return strcasecmp(
            $username1 !== null ? trim($username1) : '',
            $username2 !== null ? trim($username2) : ''
        ) === 0;
    }
}
