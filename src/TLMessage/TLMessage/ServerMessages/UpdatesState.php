<?php

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages;

use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

class UpdatesState extends TLServerMessage
{
    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    public static function isIt(AnonymousMessage $tlMessage)
    {
        return self::checkType($tlMessage, 'updates.state');
    }

    /**
     * @return int
     */
    public function getPts()
    {
        return $this->getTlMessage()->getValue('pts');
    }

    /**
     * @return int
     */
    public function getQts()
    {
        return $this->getTlMessage()->getValue('qts');
    }

    /**
     * @return int
     */
    public function getDate()
    {
        return $this->getTlMessage()->getValue('date');
    }
}
