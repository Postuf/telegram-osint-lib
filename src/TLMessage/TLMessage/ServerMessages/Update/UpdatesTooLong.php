<?php

namespace TLMessage\TLMessage\ServerMessages\Update;

use MTSerialization\AnonymousMessage;
use TLMessage\TLMessage\TLServerMessage;

class UpdatesTooLong extends TLServerMessage
{
    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    public static function isIt(AnonymousMessage $tlMessage)
    {
        return self::checkType($tlMessage, 'updatesTooLong');
    }
}
