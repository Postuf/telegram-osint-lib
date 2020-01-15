<?php

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
    public static function isIt(AnonymousMessage $tlMessage)
    {
        return self::checkType($tlMessage, 'auth.sentCode');
    }

    /**
     * @return bool
     */
    public function isPhoneRegistered()
    {
        return BoolTrue::isIt($this->getTlMessage()->getNode('phone_registered'));
    }

    /**
     * @return string
     */
    public function getPhoneCodeHash()
    {
        return $this->getTlMessage()->getValue('phone_code_hash');
    }
}
