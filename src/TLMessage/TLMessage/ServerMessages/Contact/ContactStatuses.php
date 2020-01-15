<?php

/** @noinspection PhpUnused */

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages\Contact;

use TelegramOSINT\Exception\TGException;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

class ContactStatuses extends TLServerMessage
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
     * @return ContactStatus[]
     */
    public function getStatuses()
    {
        $index = 0;
        $statuses = [];

        while(true){
            try {
                $statuses[] = new ContactStatus($this->getTlMessage()->getNode($index));
                $index++;
            } catch(TGException $exception){
                break;
            }
        }

        return $statuses;
    }
}
