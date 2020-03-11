<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages\Rpc;

use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

class RpcResult extends TLServerMessage
{
    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    public static function isIt(AnonymousMessage $tlMessage): bool
    {
        return self::checkType($tlMessage, 'rpc_result');
    }

    public function getRequestMsgId(): int
    {
        return $this->getTlMessage()->getValue('req_msg_id');
    }

    public function getResult(): AnonymousMessage
    {
        return $this->getTlMessage()->getNode('result');
    }
}
