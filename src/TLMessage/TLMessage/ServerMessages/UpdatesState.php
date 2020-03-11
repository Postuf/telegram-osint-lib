<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages;

use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

class UpdatesState extends TLServerMessage
{
    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    public static function isIt(AnonymousMessage $tlMessage): bool
    {
        return self::checkType($tlMessage, 'updates.state');
    }

    public function getPts(): int
    {
        return $this->getTlMessage()->getValue('pts');
    }

    public function getQts(): int
    {
        return $this->getTlMessage()->getValue('qts');
    }

    public function getDate(): int
    {
        return $this->getTlMessage()->getValue('date');
    }
}
