<?php

declare(strict_types=1);

/** @noinspection PhpUnused
 * @noinspection UnknownInspectionInspection
 */

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
    public static function isIt(AnonymousMessage $tlMessage): bool
    {
        return self::checkType($tlMessage, 'vector');
    }

    /**
     * @return ContactStatus[]
     * @noinspection PhpUnused
     * @noinspection UnknownInspectionInspection
     */
    public function getStatuses(): array
    {
        $index = 0;
        $statuses = [];

        while(true){
            try {
                $statuses[] = new ContactStatus($this->getTlMessage()->getNode((string) $index));
                $index++;
            } catch(TGException $exception){
                break;
            }
        }

        return $statuses;
    }
}
