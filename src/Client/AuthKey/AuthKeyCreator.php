<?php

namespace Client\AuthKey;


use Client\AuthKey\Versions\AuthKey_v0_RawB64;
use Client\AuthKey\Versions\AuthKey_v1_Extended;
use Client\AuthKey\Versions\AuthKey_v1_Simple;
use Client\AuthKey\Versions\AuthKey_v2;
use Client\AuthKey\Versions\AuthKey_v2_Authorized;
use Client\AuthKey\Versions\AuthKey_v2_Phone;
use Exception\TGException;
use TGConnection\DataCentre;


/**
 * Class which manages different AuthKey versions
 */
class AuthKeyCreator
{

    /**
     * @param string $serializedAuthKey
     * @return AuthKey
     * @throws TGException
     */
    public static function createFromString(string $serializedAuthKey)
    {
        if(self::is_AuthKey_v2_Authorized($serializedAuthKey))
            return new AuthKey_v2_Authorized($serializedAuthKey);

        if(self::is_AuthKey_v2_Phone($serializedAuthKey))
            return new AuthKey_v2_Phone($serializedAuthKey);

        if(self::is_AuthKey_v2($serializedAuthKey))
            return new AuthKey_v2($serializedAuthKey);

        if(self::is_AuthKey_v1_Extended($serializedAuthKey))
            return new AuthKey_v1_Extended($serializedAuthKey);

        if(self::is_AuthKey_v1_Simple($serializedAuthKey))
            return new AuthKey_v1_Simple($serializedAuthKey);

        if(self::is_AuthKey_v0_RawB64($serializedAuthKey))
            return new AuthKey_v0_RawB64($serializedAuthKey);

        throw new TGException(TGException::ERR_AUTH_KEY_BAD_FORMAT);
    }


    /**
     * @param string $authKey
     * @param string $initialSalt
     * @param DataCentre $associatedWithDC
     * @return AuthKey
     * @throws TGException
     */
    public static function createActual(
        string $authKey,
        /** @noinspection PhpUnusedParameterInspection */ string $initialSalt,
        DataCentre $associatedWithDC
    ) {
        return AuthKey_v2::serialize($authKey, $associatedWithDC);
    }


    /**
     * @param AuthKey $authKey
     * @param AuthInfo $authInfo
     * @return AuthKey
     * @throws TGException
     */
    public static function attachAuthInfo(AuthKey $authKey, AuthInfo $authInfo)
    {
        // there is no point in supporting different authKey versions,
        // because this lib will use the only one eventually
        if(!($authKey instanceof AuthKey_v2))
            throw new TGException(TGException::ERR_AUTH_KEY_NOT_SUPPORTED);

        return AuthKey_v2_Authorized::serialize($authKey, $authInfo);
    }


    private static function is_AuthKey_v2_Authorized(string $serialized)
    {
        try{
            new AuthKey_v2_Authorized($serialized);
            return true;
        }catch (TGException $exception){
            return false;
        }
    }


    private static function is_AuthKey_v2_Phone(string $serialized)
    {
        try{
            new AuthKey_v2_Phone($serialized);
            return true;
        }catch (TGException $exception){
            return false;
        }
    }


    private static function is_AuthKey_v2(string $serialized)
    {
        try{
            new AuthKey_v2($serialized);
            return true;
        }catch (TGException $exception){
            return false;
        }
    }


    private static function is_AuthKey_v1_Extended(string $serialized)
    {
        try{
            new AuthKey_v1_Extended($serialized);
            return true;
        }catch (TGException $exception){
            return false;
        }
    }


    private static function is_AuthKey_v1_Simple(string $serialized)
    {
        try{
            new AuthKey_v1_Simple($serialized);
            return true;
        }catch (TGException $exception){
            return false;
        }
    }


    private static function is_AuthKey_v0_RawB64(string $serialized)
    {
        try{
            new AuthKey_v0_RawB64($serialized);
            return true;
        }catch (TGException $exception){
            return false;
        }
    }

}