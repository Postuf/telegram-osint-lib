<?php

namespace Client\AuthKey\Versions;


use Client\AuthKey\AuthKey;
use Exception\TGException;
use TGConnection\DataCentre;


/**
 * base64(authKey)
 */
class AuthKey_v0_RawB64 implements AuthKey
{
    /** @var string */
    private $serializedAuthKey;


    /**
     * @param string $serializedAuthKey
     * @throws TGException
     */
    public function __construct($serializedAuthKey)
    {
        $this->checkSerializedAuthKey($serializedAuthKey);
        $this->serializedAuthKey = $serializedAuthKey;
    }


    /**
     * @param string $authKey
     * @return AuthKey
     * @throws TGException
     */
    public static function serialize(string $authKey)
    {
        return new AuthKey_v0_RawB64(base64_encode($authKey));
    }


    /**
     * @param string $serializedAuthKey
     * @throws TGException
     */
    private function checkSerializedAuthKey($serializedAuthKey)
    {
        if(!base64_decode($serializedAuthKey))
            throw new TGException(TGException::ERR_AUTH_KEY_BAD_FORMAT);
        if(strlen(base64_decode($serializedAuthKey)) != 256)
            throw new TGException(TGException::ERR_AUTH_KEY_BAD_FORMAT);
    }


    /**
     * @return string
     */
    public function getRawAuthKey()
    {
        return base64_decode($this->serializedAuthKey);
    }

    /**
     * @return string
     */
    public function getSerializedAuthKey()
    {
        return $this->serializedAuthKey;
    }


    /**
     * @return DataCentre
     */
    public function getAttachedDC()
    {
        return DataCentre::getDefault();
    }
}