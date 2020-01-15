<?php

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages\UserStatus;

use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

class UserStatusOnline extends TLServerMessage
{
    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    public static function isIt(AnonymousMessage $tlMessage)
    {
        return self::checkType($tlMessage, 'userStatusOnline');
    }

    /**
     * @return int
     */
    public function getExpires()
    {
        return $this->getTlMessage()->getValue('expires');
    }
}
