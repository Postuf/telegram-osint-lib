<?php

namespace TLMessage\TLMessage\ServerMessages;


use MTSerialization\AnonymousMessage;
use TLMessage\TLMessage\TLServerMessage;


class UploadedFile extends TLServerMessage
{

    const FORMAT_JPEG = 'jpg';
    const FORMAT_UNKNOWN = 'unknown';


    /**
     * @param AnonymousMessage $tlMessage
     * @return boolean
     */
    public static function isIt(AnonymousMessage $tlMessage)
    {
        return self::checkType($tlMessage, 'upload.file');
    }


    /**
     * @return string
     */
    public function getBytes()
    {
        return $this->getTlMessage()->getValue('bytes');
    }


    /**
     * @return int
     */
    public function getModificationTs()
    {
        return (int)$this->getTlMessage()->getValue('mtime');
    }


    /**
     * @return boolean
     */
    public function isJpeg()
    {
        return $this->getTlMessage()->getNode('type')->getType() == 'storage.fileJpeg';
    }


    /**
     * @return boolean
     */
    public function getFormatName()
    {
        switch($this->getTlMessage()->getNode('type')->getType())
        {
            case 'storage.fileJpeg':
                return self::FORMAT_JPEG;
            default:
                return self::FORMAT_UNKNOWN;

        }
    }



}