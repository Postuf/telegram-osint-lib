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
    private function checkSerializedAuthKey($serializedAuthKey): void
    {
        if (!base64_decode($serializedAuthKey)) {
            throw new TGException(TGException::ERR_AUTH_KEY_BAD_FORMAT);
        }
        if (strlen(base64_decode($serializedAuthKey)) !== 256 + self::RAW_KEY_LENGTH) {
            throw new TGException(TGException::ERR_AUTH_KEY_BAD_FORMAT);
        }
    }

    public function getRawAuthKey(): string
    {
        $decoded = base64_decode($this->serializedAuthKey);

        return (string) substr($decoded, self::RAW_KEY_LENGTH);
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
