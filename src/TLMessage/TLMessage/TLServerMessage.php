<?php

namespace TelegramOSINT\TLMessage\TLMessage;

use TelegramOSINT\Exception\TGException;
use TelegramOSINT\MTSerialization\AnonymousMessage;

abstract class TLServerMessage
{
    /**
     * @var AnonymousMessage
     */
    private $tlMessage;

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
    abstract public static function isIt(AnonymousMessage $tlMessage);

    /**
     * @param AnonymousMessage $anonymousMessage
     * @param string           $type
     *
     * @return bool
     */
    protected static function checkType(AnonymousMessage $anonymousMessage, string $type)
    {
        return $anonymousMessage->getType() == $type;
    }

    /**
     * @param AnonymousMessage $anonymousMessage
     *
     * @throws TGException
     */
    private function throwIfIncorrectType(AnonymousMessage $anonymousMessage)
    {
        if(!static::isIt($anonymousMessage))
            throw new TGException(TGException::ERR_TL_MESSAGE_UNEXPECTED_OBJECT, $anonymousMessage->getType());
    }

    /**
     * @return AnonymousMessage
     */
    protected function getTlMessage(): AnonymousMessage
    {
        return $this->tlMessage;
    }
}
