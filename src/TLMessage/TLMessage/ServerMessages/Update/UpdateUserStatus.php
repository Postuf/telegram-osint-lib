<?php

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages\Update;

use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Custom\UserStatus;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

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
