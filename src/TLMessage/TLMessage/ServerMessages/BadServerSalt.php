<?php

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages;

use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

class BadServerSalt extends TLServerMessage
{
    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    public static function isIt(AnonymousMessage $tlMessage)
    {
        return self::checkType($tlMessage, 'bad_server_salt');
    }

    /**
     * @return int
     */
    public function getNewServerSalt()
    {
        $newSalt = $this->getTlMessage()->getValue('new_server_salt');

        return pack('Q', $newSalt);
    }

    /**
     * @return string
     */
    public function getBadMsdId()
    {
        return $this->getTlMessage()->getValue('bad_msg_id');
    }
}
