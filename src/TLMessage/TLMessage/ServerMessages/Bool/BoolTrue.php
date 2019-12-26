<?php

namespace TLMessage\TLMessage\ServerMessages\Bool;

use MTSerialization\AnonymousMessage;
use TLMessage\TLMessage\TLServerMessage;

class BoolTrue extends TLServerMessage
{
    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    public static function isIt(AnonymousMessage $tlMessage)
    {
        return self::checkType($tlMessage, 'boolTrue');
    }
}
