<?php

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages;

use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

class AuthorizationSelfUser extends TLServerMessage
{
    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    public static function isIt(AnonymousMessage $tlMessage)
    {
        return self::checkType($tlMessage, 'auth.authorization');
    }

    /**
     * @return UserSelf
     */
    public function getUser()
    {
        $self = $this->getTlMessage()->getNode('user');

        return new UserSelf($self);
    }
}
