<?php

namespace TLMessage\TLMessage\ServerMessages\UserStatus;

use MTSerialization\AnonymousMessage;
use TLMessage\TLMessage\TLServerMessage;

class UserStatusEmpty extends TLServerMessage
{
    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    public static function isIt(AnonymousMessage $tlMessage)
    {
        return self::checkType($tlMessage, 'userStatusEmpty');
    }
}
