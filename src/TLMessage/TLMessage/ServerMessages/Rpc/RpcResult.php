<?php

namespace TLMessage\TLMessage\ServerMessages\Rpc;


use MTSerialization\AnonymousMessage;
use TLMessage\TLMessage\TLServerMessage;


class RpcResult extends TLServerMessage
{

    /**
     * @param AnonymousMessage $tlMessage
     * @return boolean
     */
    public static function isIt(AnonymousMessage $tlMessage)
    {
        return self::checkType($tlMessage, 'rpc_result');
    }


    /**
     * @return string
     */
    public function getRequestMsgId()
    {
        return $this->getTlMessage()->getValue('req_msg_id');
    }


    /**
     * @return AnonymousMessage
     */
    public function getResult()
    {
        return $this->getTlMessage()->getNode('result');
    }


}