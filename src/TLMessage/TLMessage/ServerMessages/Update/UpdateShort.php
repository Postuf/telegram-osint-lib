<?php

namespace TLMessage\TLMessage\ServerMessages\Update;


use MTSerialization\AnonymousMessage;
use TLMessage\TLMessage\TLServerMessage;


class UpdateShort extends TLServerMessage
{

    /**
     * @param AnonymousMessage $tlMessage
     * @return boolean
     */
    public static function isIt(AnonymousMessage $tlMessage)
    {
        return self::checkType($tlMessage, 'updateShort');
    }

    /**
     * @return AnonymousMessage
     */
    public function getUpdate()
    {
        return $this->getTlMessage()->getNode('update');
    }


}