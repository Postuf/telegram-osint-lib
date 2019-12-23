<?php

namespace TLMessage\TLMessage\ServerMessages\UserStatus;


use MTSerialization\AnonymousMessage;
use TLMessage\TLMessage\TLServerMessage;


class UserStatusOffline extends TLServerMessage
{

    /**
     * @param AnonymousMessage $tlMessage
     * @return boolean
     */
    public static function isIt(AnonymousMessage $tlMessage)
    {
        return self::checkType($tlMessage, 'userStatusOffline');
    }

    /**
     * @return int
     */
    public function getWasOnline()
    {
        return $this->getTlMessage()->getValue('was_online');
    }


}