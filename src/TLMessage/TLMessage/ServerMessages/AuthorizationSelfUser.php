<?php


namespace TLMessage\TLMessage\ServerMessages;


use MTSerialization\AnonymousMessage;
use TLMessage\TLMessage\TLServerMessage;


class AuthorizationSelfUser extends TLServerMessage
{

    /**
     * @param AnonymousMessage $tlMessage
     * @return boolean
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