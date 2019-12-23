<?php

namespace Client\AuthKey\Versions;


use Client\AuthKey\AuthInfo;
use Client\AuthKey\AuthKey;
use Exception\TGException;
use TGConnection\DataCentre;


/**
 * <phone>:serialized(authKey_v2)
 */
class AuthKey_v2_Phone implements AuthKey
{

    /**
     * @var string
     */
    private $phone;
    /**
     * @var AuthKey_v2
     */
    private $innerAuthKey;


    /**
     * @param string $serializedAuthKey
     * @throws TGException
     */
    public function __construct(string $serializedAuthKey)
    {
        $parts = explode(':', $serializedAuthKey);
        if(count($parts) < 2)
            throw new TGException(TGException::ERR_AUTH_KEY_BAD_FORMAT);

        $this->phone = $parts[0];
        $this->innerAuthKey = new AuthKey_v2(implode(':', array_slice($parts, 1)));
    }


    /**
     * @param AuthKey_v2 $authKey
     * @param AuthInfo $authInfo
     * @return AuthKey_v2_Phone
     * @throws TGException
     */
    public static function serialize(AuthKey_v2 $authKey, AuthInfo $authInfo)
    {
        $serialized = trim($authInfo->getPhone()) . ':' . $authKey->getSerializedAuthKey();
        return new AuthKey_v2_Phone($serialized);
    }


    /**
     * @return string
     */
    public function getSerializedAuthKey()
    {
        return trim($this->phone) . ':' . $this->innerAuthKey->getSerializedAuthKey();
    }


    /**
     * @return string
     */
    public function getRawAuthKey()
    {
        return $this->innerAuthKey->getRawAuthKey();
    }


    /**
     * @return DataCentre
     */
    public function getAttachedDC()
    {
        return $this->innerAuthKey->getAttachedDC();
    }

}