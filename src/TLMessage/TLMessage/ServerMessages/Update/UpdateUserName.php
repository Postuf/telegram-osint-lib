<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages\Update;

use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

class UpdateUserName extends TLServerMessage
{
    public static function isIt(AnonymousMessage $tlMessage): bool
    {
        return self::checkType($tlMessage, 'updateUserName');
    }

    public function getUserId(): int
    {
        return (int) $this->getTlMessage()->getValue('user_id');
    }

    public function getUsername(): string
    {
        return $this->getTlMessage()->getValue('username');
    }
}
