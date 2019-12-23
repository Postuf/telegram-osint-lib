<?php

namespace TLMessage\TLMessage\ServerMessages;


use Exception\TGException;
use MTSerialization\AnonymousMessage;
use TLMessage\TLMessage\ServerMessages\Contact\ContactUser;
use TLMessage\TLMessage\TLServerMessage;


class UserFull extends TLServerMessage
{

    /**
     * @param AnonymousMessage $tlMessage
     * @return boolean
     */
    public static function isIt(AnonymousMessage $tlMessage)
    {
        return self::checkType($tlMessage, 'userFull');
    }


    /**
     * @return ContactUser
     * @throws TGException
     */
    public function getUser()
    {
        $user = $this->getTlMessage()->getNode('user');
        return new ContactUser($user);
    }


    /**
     * @return string
     */
    public function getAbout()
    {
        return $this->getTlMessage()->getValue('about');
    }

}