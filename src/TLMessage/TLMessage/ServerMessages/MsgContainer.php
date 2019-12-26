<?php

namespace TLMessage\TLMessage\ServerMessages;

use MTSerialization\AnonymousMessage;
use TLMessage\TLMessage\TLServerMessage;

class MsgContainer extends TLServerMessage
{
    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    public static function isIt(AnonymousMessage $tlMessage)
    {
        return self::checkType($tlMessage, 'msg_container');
    }

    /**
     * @return AnonymousMessage[]
     */
    public function getMessages()
    {
        return $this->getTlMessage()->getNodes('messages');
    }
}
