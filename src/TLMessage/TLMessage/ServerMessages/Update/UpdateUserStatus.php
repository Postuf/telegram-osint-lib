<?php

namespace TLMessage\TLMessage\ServerMessages\Update;

use MTSerialization\AnonymousMessage;
use TLMessage\TLMessage\ServerMessages\Custom\UserStatus;
use TLMessage\TLMessage\TLServerMessage;

class UpdateUserStatus extends TLServerMessage
{
    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    public static function isIt(AnonymousMessage $tlMessage)
    {
        return self::checkType($tlMessage, 'updateUserStatus');
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->getTlMessage()->getValue('user_id');
    }

    /**
     * @return UserStatus
     */
    public function getStatus()
    {
        $status = $this->getTlMessage()->getNode('status');

        return new UserStatus($status);
    }
}
