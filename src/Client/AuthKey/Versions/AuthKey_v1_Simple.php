<?php

namespace TelegramOSINT\Client\AuthKey\Versions;

use TelegramOSINT\Client\AuthKey\AuthKey;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\TGConnection\DataCentre;

/**
 * base64(serverSalt+authKey)
 */
class AuthKey_v1_Simple implements AuthKey
{
    private const RAW_KEY_LENGTH = 8;
    /**
     * @var string
     */
    private $serializedAuthKey;

    /**
     * @param string $serializedAuthKey
     *
     * @throws TGException
     */
    public function __construct($serializedAuthKey)
    {
        $this->checkSerializedAuthKey($serializedAuthKey);
        $this->serializedAuthKey = $serializedAuthKey;
    }

    /**
     * @param string $serializedAuthKey
     *
     * @throws TGException
     */
    private function checkSerializedAuthKey($serializedAuthKey)
    {
        if(!base64_decode($serializedAuthKey))
            throw new TGException(TGException::ERR_AUTH_KEY_BAD_FORMAT);
        if(strlen(base64_decode($serializedAuthKey)) != 256 + self::RAW_KEY_LENGTH)
            throw new TGException(TGException::ERR_AUTH_KEY_BAD_FORMAT);
    }

    /**
     * @return string
     */
    public function getRawAuthKey()
    {
        $decoded = base64_decode($this->serializedAuthKey);

        return substr($decoded, self::RAW_KEY_LENGTH);
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
