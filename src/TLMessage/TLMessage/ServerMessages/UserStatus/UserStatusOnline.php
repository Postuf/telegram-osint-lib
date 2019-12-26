<?php

namespace TLMessage\TLMessage\ServerMessages\UserStatus;

use MTSerialization\AnonymousMessage;
use TLMessage\TLMessage\TLServerMessage;

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
