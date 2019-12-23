<?php


namespace TLMessage\TLMessage\ServerMessages;


use Exception\TGException;
use MTSerialization\AnonymousMessage;
use TLMessage\TLMessage\TLServerMessage;


class Languages extends TLServerMessage
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
     * @return boolean
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