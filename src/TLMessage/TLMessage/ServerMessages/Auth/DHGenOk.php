<?php

namespace TLMessage\TLMessage\ServerMessages\Auth;


use MTSerialization\AnonymousMessage;
use TLMessage\TLMessage\TLServerMessage;


class DHGenOk extends TLServerMessage
{

    /**
     * @param AnonymousMessage $tlMessage
     * @return boolean
     */
    public static function isIt(AnonymousMessage $tlMessage)
    {
        return self::checkType($tlMessage, 'dh_gen_ok');
    }


    /**
     * @return string
     */
    public function getClientNonce()
    {
        return $this->getTlMessage()->getValue('nonce');
    }


    /**
     * @return string
     */
    public function getServerNonce()
    {
        return $this->getTlMessage()->getValue('server_nonce');
    }

}