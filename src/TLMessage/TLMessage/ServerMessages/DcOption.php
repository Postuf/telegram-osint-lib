<?php

namespace TLMessage\TLMessage\ServerMessages;


use MTSerialization\AnonymousMessage;
use TLMessage\TLMessage\TLServerMessage;


class DcOption extends TLServerMessage
{

    /**
     * @param AnonymousMessage $tlMessage
     * @return boolean
     */
    public static function isIt(AnonymousMessage $tlMessage)
    {
        return self::checkType($tlMessage, 'dcOption');
    }


    /**
     * @return int
     */
    public function getId()
    {
        return $this->getTlMessage()->getValue('id');
    }


    /**
     * @return int
     */
    public function getPort()
    {
        return $this->getTlMessage()->getValue('port');
    }


    /**
     * @return string
     */
    public function getIp()
    {
        return $this->getTlMessage()->getValue('ip_address');
    }



}