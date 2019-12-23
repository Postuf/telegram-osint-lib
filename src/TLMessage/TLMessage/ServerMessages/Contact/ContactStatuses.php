<?php /** @noinspection PhpUnused */

namespace TLMessage\TLMessage\ServerMessages\Contact;


use Exception\TGException;
use MTSerialization\AnonymousMessage;
use TLMessage\TLMessage\TLServerMessage;


class ContactStatuses extends TLServerMessage
{

    /**
     * @param AnonymousMessage $tlMessage
     * @return boolean
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