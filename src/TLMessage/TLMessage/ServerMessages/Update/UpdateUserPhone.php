<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages\Update;

use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

class UpdateUserPhone extends TLServerMessage
{
    public static function isIt(AnonymousMessage $tlMessage): bool
    {
        return self::checkType($tlMessage, 'updateUserPhone');
    }

    public function getUserId(): int
    {
        return (int) $this->getTlMessage()->getValue('user_id');
    }

    public function getPhone(): string
    {
        return $this->getTlMessage()->getValue('phone');
    }
}
