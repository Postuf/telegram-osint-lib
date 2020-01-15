<?php

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages\Rpc\Errors;

use TelegramOSINT\Exception\TGException;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Rpc\RpcError;

class FloodWait
{
    /**
     * @var RpcError
     */
    private $error;

    /**
     * @param RpcError $rpcError
     */
    public function __construct(RpcError $rpcError)
    {
        if(!$rpcError->isFloodError())
            throw new TGException(TGException::ERR_TL_MESSAGE_UNEXPECTED_OBJECT, 'not a flood error');
        $this->error = $rpcError;
    }

    /**
     * @return int
     */
    public function getWaitTimeSec()
    {
        $parts = explode('_', $this->error->getErrorString());

        return (int) $parts[count($parts) - 1];
    }
}
