<?php

namespace TLMessage\TLMessage\ServerMessages;


use MTSerialization\AnonymousMessage;
use TLMessage\TLMessage\TLServerMessage;


class ExportedAuthorization extends TLServerMessage
{

    /**
     * @param AnonymousMessage $tlMessage
     * @return boolean
     */
    public static function isIt(AnonymousMessage $tlMessage)
    {
        return self::checkType($tlMessage, 'auth.exportedAuthorization');
    }


    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->getTlMessage()->getValue('id');
    }


    /**
     * @return string
     */
    public function getTransferKey()
    {
        return $this->getTlMessage()->getValue('bytes');
    }


}