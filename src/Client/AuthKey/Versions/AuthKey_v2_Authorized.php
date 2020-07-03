<?php

namespace TelegramOSINT\Client\AuthKey\Versions;

use TelegramOSINT\Client\AuthKey\AuthInfo;
use TelegramOSINT\Client\AuthKey\AuthorizedAuthKey;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\Registration\AccountInfo;
use TelegramOSINT\TGConnection\DataCentre;
use Throwable;

/**
 * <phone>:serialized(AuthInfo):serialized(authKey_v2)
 *
 * @see AuthKey_v2
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
     *
     * @throws TGException
     */
    public function __construct(string $serializedAuthKey)
    {
        try{
            $parts = explode(':', $serializedAuthKey);
            if(count($parts) < 3) {
                throw new TGException(TGException::ERR_AUTH_KEY_BAD_FORMAT);
            }
            $this->serialized = $serializedAuthKey;
            $this->phone = (string) $parts[0];
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
     * @param AuthInfo   $authInfo
     *
     * @throws TGException
     *
     * @return AuthKey_v2_Authorized
     */
    public static function serialize(AuthKey_v2 $authKey, AuthInfo $authInfo): self
    {
        $serialized =
            trim($authInfo->getPhone()).':'.
            bin2hex($authInfo->getAccount()->serializeToJson()).':'.
            $authKey->getSerializedAuthKey();

        return new self($serialized);
    }

    public function getSerializedAuthKey(): string
    {
        return $this->serialized;
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

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getAccountInfo(): AccountInfo
    {
        return $this->account;
    }
}
