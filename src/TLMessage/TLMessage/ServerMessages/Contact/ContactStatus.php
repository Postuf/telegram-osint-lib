<?php

namespace TLMessage\TLMessage\ServerMessages\Contact;


use Exception\TGException;
use MTSerialization\AnonymousMessage;
use TLMessage\TLMessage\ServerMessages\Custom\UserStatus;
use TLMessage\TLMessage\TLServerMessage;


class ContactStatus extends TLServerMessage
{

    /**
     * @param AnonymousMessage $tlMessage
     * @return boolean
     */
    public static function isIt(AnonymousMessage $tlMessage)
    {
        return self::checkType($tlMessage, 'contactStatus');
    }


    /**
     * @return UserStatus|null
     */
    public function getStatus()
    {
        try {
            $status = $this->getTlMessage()->getNode('status');
        } catch (TGException $e){
            return null;
        }
        return new UserStatus($status);
    }


    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->getTlMessage()->getValue('user_id');
    }

}