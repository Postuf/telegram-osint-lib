<?php

namespace TelegramOSINT\Client\AuthKey\Versions;

use TelegramOSINT\Client\AuthKey\AuthInfo;
use TelegramOSINT\Client\AuthKey\AuthKey;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\TGConnection\DataCentre;

/**
 * <phone>:serialized(authKey_v2)
 */
class AuthKey_v2_Phone implements AuthKey
{
    /**
     * @var string
     */
    private $phone;
    private AuthKey_v2 $innerAuthKey;

    /**
     * @param string $serializedAuthKey
     *
     * @throws TGException
     */
    public function __construct(string $serializedAuthKey)
    {
        $parts = explode(':', $serializedAuthKey);
        if (count($parts) < 2) {
            throw new TGException(TGException::ERR_AUTH_KEY_BAD_FORMAT);
        }
        $this->phone = $parts[0];
        $this->innerAuthKey = new AuthKey_v2(implode(':', array_slice($parts, 1)));
    }

    /**
     * @param AuthKey_v2 $authKey
     * @param AuthInfo   $authInfo
     *
     * @throws TGException
     *
     * @return AuthKey_v2_Phone
     */
    public static function serialize(AuthKey_v2 $authKey, AuthInfo $authInfo): self
    {
        $serialized = trim($authInfo->getPhone()).':'.$authKey->getSerializedAuthKey();

        return new self($serialized);
    }

    public function getSerializedAuthKey(): string
    {
        return trim($this->phone).':'.$this->innerAuthKey->getSerializedAuthKey();
    }

    public function getRawAuthKey(): string
    {
        return $this->innerAuthKey->getRawAuthKey();
    }

    /**
     * @throws TGException
     *
     * @return DataCentre
     */
    public function getAttachedDC(): DataCentre
    {
        return $this->innerAuthKey->getAttachedDC();
    }
}
