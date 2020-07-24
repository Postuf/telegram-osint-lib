<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages\Rpc\Errors;

use TelegramOSINT\Exception\TGException;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Rpc\RpcError;

class MigrateError
{
    private RpcError $error;

    /**
     * MigrateError constructor.
     *
     * @param RpcError $rpcError
     *
     * @throws TGException
     */
    public function __construct(RpcError $rpcError)
    {
        $this->error = $rpcError;
        if (!$this->error->isPhoneMigrateError() && !$this->error->isNetworkMigrateError()) {
            throw new TGException(TGException::ERR_TL_MESSAGE_UNEXPECTED_OBJECT, 'not a migrate error');
        }
    }

    public function getDcId(): int
    {
        $parts = explode('_', $this->error->getErrorString());

        return (int) end($parts);
    }
}
