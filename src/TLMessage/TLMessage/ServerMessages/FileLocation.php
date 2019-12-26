<?php

namespace TLMessage\TLMessage\ServerMessages;

use Exception\TGException;
use MTSerialization\AnonymousMessage;
use TLMessage\TLMessage\TLServerMessage;

class FileLocation extends TLServerMessage
{
    /**
     * @var bool
     */
    private $deprecated = false;

    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    public static function isIt(AnonymousMessage $tlMessage)
    {
        return
            self::checkType($tlMessage, 'fileLocation') ||
            self::checkType($tlMessage, 'fileLocationToBeDeprecated');
    }

    public function __construct(AnonymousMessage $tlMessage)
    {
        parent::__construct($tlMessage);
        $this->deprecated = self::checkType($tlMessage, 'fileLocationToBeDeprecated');
    }

    /**
     * @return int
     */
    public function getDcId()
    {
        $this->throwIfDeprecated();

        return $this->getTlMessage()->getValue('dc_id');
    }

    /**
     * @return int
     */
    public function getVolumeId()
    {
        return $this->getTlMessage()->getValue('volume_id');
    }

    /**
     * @return int
     */
    public function getLocalId()
    {
        return $this->getTlMessage()->getValue('local_id');
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        $this->throwIfDeprecated();

        return $this->getTlMessage()->getValue('secret');
    }

    /**
     * @return string
     */
    public function getReference()
    {
        $this->throwIfDeprecated();

        return $this->getTlMessage()->getValue('file_reference');
    }

    /**
     * @throws TGException
     */
    private function throwIfDeprecated()
    {
        if($this->deprecated)
            throw new TGException(TGException::ERR_TL_MESSAGE_FIELD_NOT_EXISTS, 'Deprecated node!');
    }
}
