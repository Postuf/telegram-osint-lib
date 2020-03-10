<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages;

use TelegramOSINT\Exception\TGException;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

class Languages extends TLServerMessage
{
    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    public static function isIt(AnonymousMessage $tlMessage): bool
    {
        return self::checkType($tlMessage, 'vector');
    }

    public function getCount(): int
    {
        $langIdx = 0;
        while(true){
            try {
                $this->getTlMessage()->getNode((string) $langIdx);
                $langIdx++;
            } /** @noinspection PhpRedundantCatchClauseInspection */ catch (TGException $exception){
                break;
            }
        }

        return $langIdx;
    }
}
