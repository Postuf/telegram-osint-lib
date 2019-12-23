<?php

namespace TLMessage\TLMessage\ServerMessages\UserStatus;


use MTSerialization\AnonymousMessage;
use TLMessage\TLMessage\TLServerMessage;


class UserStatusRecently extends TLServerMessage
{

    /**
     * @param AnonymousMessage $tlMessage
     * @return boolean
     */
    public static function isIt(AnonymousMessage $tlMessage)
    {
        return self::checkType($tlMessage, 'userStatusRecently');
    }

}