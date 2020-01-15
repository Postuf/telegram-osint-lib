<?php

namespace TelegramOSINT\Client\AuthKey\Versions;

use TelegramOSINT\Client\AuthKey\AuthKey;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\TGConnection\DataCentre;

/**
 * <phone>:base64(serverSalt+authKey)
 */
class AuthKey_v1_Extended implements AuthKey
{
    /** @var string */
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
     * @param string $authKey
     *
     * @throws TGException
     */
    private function checkSerializedAuthKey($authKey)
    {
        $authKey = explode(':', $authKey);

        if(count($authKey) != 2)
            throw new TGException(TGException::ERR_AUTH_KEY_BAD_FORMAT);
        if(strlen(base64_decode($authKey[1])) != 256 + 8)
            throw new TGException(TGException::ERR_AUTH_KEY_BAD_FORMAT);
    }

    /**
     * @throws
     *
     * @return string
     */
    public function getRawAuthKey()
    {
        $authKey = explode(':', $this->serializedAuthKey)[1];
        $decoded = base64_decode($authKey);

        return substr($decoded, 8);
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
