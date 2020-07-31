<?php

namespace TelegramOSINT\TLMessage\TLMessage;

use TelegramOSINT\Exception\TGException;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Rpc\RpcError;

abstract class TLServerMessage
{
    private AnonymousMessage $tlMessage;

    /**
     * TLServerMessage constructor.
     *
     * @param AnonymousMessage $tlMessage
     *
     * @throws TGException
     */
    public function __construct(AnonymousMessage $tlMessage)
    {
        $this->throwIfIncorrectType($tlMessage);
        $this->tlMessage = $tlMessage;
    }

    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    abstract public static function isIt(AnonymousMessage $tlMessage): bool;

    /**
     * @param AnonymousMessage $anonymousMessage
     * @param string           $type
     *
     * @return bool
     */
    protected static function checkType(AnonymousMessage $anonymousMessage, string $type): bool
    {
        return $anonymousMessage->getType() === $type;
    }

    /**
     * @param AnonymousMessage $anonymousMessage
     *
     * @throws TGException
     */
    protected function throwIfIncorrectType(AnonymousMessage $anonymousMessage): void
    {
        if (!static::isIt($anonymousMessage)) {
            $msg = $anonymousMessage->getType().' instead of '.static::class.' class';
            if (RpcError::isIt($anonymousMessage)) {
                $msg .= ' with error '.$anonymousMessage->getValue('error_message');
            }

            throw new TGException(TGException::ERR_TL_MESSAGE_UNEXPECTED_OBJECT, $msg);
        }
    }

    protected function getTlMessage(): AnonymousMessage
    {
        return $this->tlMessage;
    }
}
