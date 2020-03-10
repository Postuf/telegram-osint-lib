<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages;

use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Bool\BoolTrue;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

class SentCodeApi extends TLServerMessage
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

    public function isPhoneRegistered(): bool
    {
        return BoolTrue::isIt($this->getTlMessage()->getNode('phone_registered'));
    }

    public function getPhoneCodeHash(): string
    {
        return $this->getTlMessage()->getValue('phone_code_hash');
    }
}
