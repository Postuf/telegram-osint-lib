<?php

namespace TelegramOSINT\Client\AuthKey\Versions;

use TelegramOSINT\Client\AuthKey\AuthKey;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\TGConnection\DataCentre;

/**
 * base64(authKey)
 */
class AuthKey_v0_RawB64 implements AuthKey
{
    /** @var string */
    private string $serializedAuthKey;

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
     *
     * @return AuthKey
     */
    public static function serialize(string $authKey)
    {
        return new self(base64_encode($authKey));
    }

    /**
     * @param string $serializedAuthKey
     *
     * @throws TGException
     */
    private function checkSerializedAuthKey($serializedAuthKey): void
    {
        if (!base64_decode($serializedAuthKey)) {
            throw new TGException(TGException::ERR_AUTH_KEY_BAD_FORMAT);
        }
        if (strlen(base64_decode($serializedAuthKey)) !== 256) {
            throw new TGException(TGException::ERR_AUTH_KEY_BAD_FORMAT);
        }
    }

    public function getRawAuthKey(): string
    {
        return (string) base64_decode($this->serializedAuthKey);
    }

    public function getSerializedAuthKey(): string
    {
        return $this->serializedAuthKey;
    }

    public function getAttachedDC(): DataCentre
    {
        return DataCentre::getDefault();
    }
}
