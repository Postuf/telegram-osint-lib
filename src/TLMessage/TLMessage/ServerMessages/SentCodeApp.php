<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages;

use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

class SentCodeApp extends TLServerMessage
{
    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    public static function isIt(AnonymousMessage $tlMessage): bool
    {
        return self::checkType($tlMessage, 'auth.sentCode');
    }

    public function isSentCodeTypeSms(): bool
    {
        return $this->getTlMessage()->getNode('type')->getType() === 'auth.sentCodeTypeSms';
    }

    public function isSentCodeTypeApp(): bool
    {
        return $this->getTlMessage()->getNode('type')->getType() === 'auth.sentCodeTypeApp';
    }

    public function getPhoneCodeHash(): string
    {
        return $this->getTlMessage()->getValue('phone_code_hash');
    }
}
