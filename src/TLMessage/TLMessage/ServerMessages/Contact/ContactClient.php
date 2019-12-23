<?php

namespace TLMessage\TLMessage\ServerMessages\Contact;


use MTSerialization\AnonymousMessage;
use TLMessage\TLMessage\TLServerMessage;


class ContactClient extends TLServerMessage
{

    /**
     * @param AnonymousMessage $tlMessage
     * @return boolean
     */
    public static function isIt(AnonymousMessage $tlMessage)
    {
        return self::checkType($tlMessage, 'importedContact');
    }


    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->getTlMessage()->getValue('user_id');
    }


    /**
     * @return int
     */
    public function getClientId()
    {
        return $this->getTlMessage()->getValue('client_id');
    }

}