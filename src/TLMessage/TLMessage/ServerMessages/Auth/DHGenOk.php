<?php

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages\Auth;

use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

class DHGenOk extends TLServerMessage
{
    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
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
