<?php

namespace TLMessage\TLMessage\ServerMessages\Auth;

use MTSerialization\AnonymousMessage;
use TLMessage\TLMessage\TLServerMessage;

class DHReq extends TLServerMessage
{
    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    public static function isIt(AnonymousMessage $tlMessage)
    {
        return self::checkType($tlMessage, 'server_DH_params_ok');
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

    /**
     * @return int
     */
    public function getEncryptedAnswer()
    {
        return $this->getTlMessage()->getValue('encrypted_answer');
    }
}
