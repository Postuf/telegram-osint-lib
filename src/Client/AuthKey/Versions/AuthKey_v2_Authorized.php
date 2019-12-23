<?php

namespace Client\AuthKey\Versions;


use Client\AuthKey\AuthInfo;
use Client\AuthKey\AuthorizedAuthKey;
use Exception\TGException;
use Registration\AccountInfo;
use TGConnection\DataCentre;
use Throwable;


/**
 * <phone>:serialized(AuthInfo):serialized(authKey_v2)
 */
class AuthKey_v2_Authorized implements AuthorizedAuthKey
{

    /**
     * @var string
     */
    private $serialized;
    /**
     * @var string
     */
    private $phone;
    /**
     * @var AccountInfo
     */
    private $account;
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
        try{
            $parts = explode(':', $serializedAuthKey);
            if(count($parts) < 3)
                throw new TGException(TGException::ERR_AUTH_KEY_BAD_FORMAT);

            $this->serialized = $serializedAuthKey;
            $this->phone = $parts[0];
            $this->account = AccountInfo::deserializeFromJson(@hex2bin($parts[1]));
            $this->innerAuthKey = new AuthKey_v2(implode(':', array_slice($parts, 2)));

        } catch (TGException $tge){
            throw $tge;
        } catch (Throwable $err){
            throw new TGException(TGException::ERR_AUTH_KEY_BAD_FORMAT);
        }
    }


    /**
     * @param AuthKey_v2 $authKey
     * @param AuthInfo $authInfo
     * @return AuthKey_v2_Authorized
     * @throws TGException
     */
    public static function serialize(AuthKey_v2 $authKey, AuthInfo $authInfo)
    {
        $serialized =
            trim($authInfo->getPhone()) . ':' .
            bin2hex($authInfo->getAccount()->serializeToJson()) . ':' .
            $authKey->getSerializedAuthKey();

        return new AuthKey_v2_Authorized($serialized);
    }


    /**
     * @return string
     */
    public function getSerializedAuthKey()
    {
        return $this->serialized;
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


    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }


    /**
     * @return AccountInfo
     */
    public function getAccountInfo()
    {
        return $this->account;
    }
}