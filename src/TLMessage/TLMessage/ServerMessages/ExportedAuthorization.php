<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages;

use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

class ExportedAuthorization extends TLServerMessage
{
    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    public static function isIt(AnonymousMessage $tlMessage): bool
    {
        return self::checkType($tlMessage, 'auth.exportedAuthorization');
    }

    public function getUserId(): int
    {
        return $this->getTlMessage()->getValue('id');
    }

    public function getTransferKey(): string
    {
        return $this->getTlMessage()->getValue('bytes');
    }
}
