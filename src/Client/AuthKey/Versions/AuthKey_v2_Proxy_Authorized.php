<?php


namespace Client\AuthKey\Versions;


use Client\AuthKey\AuthInfo;
use Client\AuthKey\AuthKey;
use Exception\TGException;
use Registration\AccountInfo;
use SocksProxyAsync\Proxy;
use Throwable;

/**
 * <phone>:serialized(AuthInfo):base64_encode(proxy):serialized(authKey_v2)
 * @see AuthKey_v2
 */
class AuthKey_v2_Proxy_Authorized implements AuthKey
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

    /** @var Proxy */
    private $proxy;
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
            if(count($parts) < 4)
                throw new TGException(TGException::ERR_AUTH_KEY_BAD_FORMAT);

            $this->serialized = $serializedAuthKey;
            $this->phone = $parts[0];
            $this->account = AccountInfo::deserializeFromJson(@hex2bin($parts[1]));
            $proxyStr = base64_decode($parts[2]);
            $this->proxy = new Proxy($proxyStr);
            $this->innerAuthKey = new AuthKey_v2(implode(':', array_slice($parts, 3)));

        } catch (TGException $tge){
            throw $tge;
        } catch (Throwable $err){
            throw new TGException(TGException::ERR_AUTH_KEY_BAD_FORMAT);
        }
    }

    /**
     * @param AuthKey_v2 $authKey
     * @param AuthInfo $authInfo
     * @param Proxy $proxy
     * @return AuthKey_v2_Proxy_Authorized
     * @throws TGException
     */
    public static function serialize(AuthKey_v2 $authKey, AuthInfo $authInfo, Proxy $proxy)
    {
        $serialized = implode(':', [
            trim($authInfo->getPhone()),
            bin2hex($authInfo->getAccount()->serializeToJson()),
            base64_encode($proxy->getServer() . ':' . $proxy->getPort()),
            $authKey->getSerializedAuthKey()
        ]);

        return new AuthKey_v2_Proxy_Authorized($serialized);
    }


    /**
     * @inheritDoc
     */
    public function getRawAuthKey()
    {
        return $this->innerAuthKey->getRawAuthKey();
    }

    /**
     * @inheritDoc
     */
    public function getSerializedAuthKey()
    {
        return $this->serialized;
    }

    /**
     * @inheritDoc
     */
    public function getAttachedDC()
    {
        return $this->innerAuthKey->getAttachedDC();
    }

    public function getProxy(): Proxy
    {
        return $this->proxy;
    }
}