<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages\Auth;

use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

class DHGenOk extends TLServerMessage
{
    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    public static function isIt(AnonymousMessage $tlMessage): bool
    {
        return self::checkType($tlMessage, 'dh_gen_ok');
    }

    public function getClientNonce(): string
    {
        return $this->getTlMessage()->getValue('nonce');
    }

    public function getServerNonce(): string
    {
        return $this->getTlMessage()->getValue('server_nonce');
    }
}
