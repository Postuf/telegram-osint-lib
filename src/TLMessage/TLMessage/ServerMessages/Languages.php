<?php

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
    public static function isIt(AnonymousMessage $tlMessage)
    {
        return self::checkType($tlMessage, 'vector');
    }

    /**
     * @return bool
     */
    public function getCount()
    {
        $langIdx = 0;
        while(true){
            try {
                $this->getTlMessage()->getNode($langIdx);
                $langIdx++;
            }catch(TGException $exception){
                break;
            }
        }

        return $langIdx;
    }
}
