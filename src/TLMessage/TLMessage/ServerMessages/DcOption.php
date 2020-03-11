<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages;

use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

class DcOption extends TLServerMessage
{
    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    public static function isIt(AnonymousMessage $tlMessage): bool
    {
        return self::checkType($tlMessage, 'dcOption');
    }

    public function getId(): int
    {
        return $this->getTlMessage()->getValue('id');
    }

    public function getPort(): int
    {
        return $this->getTlMessage()->getValue('port');
    }

    public function getIp(): string
    {
        return $this->getTlMessage()->getValue('ip_address');
    }
}
