<?php

namespace TLMessage\TLMessage\ServerMessages;


use Exception\TGException;
use MTSerialization\AnonymousMessage;
use TLMessage\TLMessage\TLServerMessage;


class UserProfilePhoto extends TLServerMessage
{
    /**
     * @var bool
     */
    private $v1 = false;

    /**
     * @param AnonymousMessage $tlMessage
     * @return boolean
     */
    public static function isIt(AnonymousMessage $tlMessage)
    {
        return
            self::checkType($tlMessage, 'userProfilePhoto_v1') ||
            self::checkType($tlMessage, 'userProfilePhoto');
    }

    public function __construct(AnonymousMessage $tlMessage)
    {
        parent::__construct($tlMessage);
        $this->v1 = self::checkType($tlMessage, 'userProfilePhoto_v1');
    }

    /**
     * @return int
     */
    public function getPhotoId()
    {
        return $this->getTlMessage()->getValue('photo_id');
    }


    /**
     * @return FileLocation
     */
    public function getBigPhoto()
    {
        $photo = $this->getTlMessage()->getNode('photo_big');
        return new FileLocation($photo);
    }


    /**
     * @return FileLocation
     */
    public function getSmallPhoto()
    {
        $photo = $this->getTlMessage()->getNode('photo_small');
        return new FileLocation($photo);
    }

    /**
     * @return int
     */
    public function getDcId()
    {
        $this->throwIfNotV2();
        return $this->getTlMessage()->getValue('dc_id');
    }

    public function isV2()
    {
        return !$this->v1;
    }

    /**
     * @throws TGException
     */
    private function throwIfNotV2()
    {
        if(!$this->isV2())
            throw new TGException(TGException::ERR_TL_MESSAGE_FIELD_NOT_EXISTS, 'Deprecated node!');
    }

}