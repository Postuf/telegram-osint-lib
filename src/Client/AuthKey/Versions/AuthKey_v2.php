<?php

namespace Client\AuthKey\Versions;


use Client\AuthKey\AuthKey;
use Exception\TGException;
use LibConfig;
use TGConnection\DataCentre;
use Throwable;


/**
 * base64(<authKey>):hex(json(<meta_info>))
 */
class AuthKey_v2 implements AuthKey
{

    private $serializedAuthKey;


    /**
     * @param string $serializedAuthKey
     * @throws TGException
     */
    public function __construct(string $serializedAuthKey)
    {
        $this->checkSerializedAuthKey($serializedAuthKey);
        $this->serializedAuthKey = $serializedAuthKey;
    }


    /**
     * @param string $authKey
     * @throws TGException
     */
    private function checkSerializedAuthKey($authKey)
    {
        try{
            $authKeyParts = explode(':', $authKey);
            if(count($authKeyParts) != 2)
                throw new TGException(TGException::ERR_AUTH_KEY_BAD_FORMAT);

            $meta = json_decode(@hex2bin($authKeyParts[1]), true);
            if(!isset($meta['created']))
                throw new TGException(TGException::ERR_AUTH_KEY_BAD_FORMAT);

            if(strlen(base64_decode($authKeyParts[0])) != 256)
                throw new TGException(TGException::ERR_AUTH_KEY_BAD_FORMAT);

            if(!@hex2bin($authKeyParts[1]) || !json_decode(@hex2bin($authKeyParts[1]), true))
                throw new TGException(TGException::ERR_AUTH_KEY_BAD_FORMAT);
        }catch (TGException $tge){
            throw $tge;
        } catch (Throwable $e){
            throw new TGException(TGException::ERR_AUTH_KEY_BAD_FORMAT);
        }
    }


    /**
     * @param string $authKey
     * @param DataCentre $dc
     * @return AuthKey_v2
     * @throws TGException
     */
    public static function serialize(string $authKey, DataCentre $dc)
    {
        $metaInfo = [
            'created' => time(),
            'api_id' => LibConfig::APP_API_ID,
            'dc_id' => $dc->getDcId(),
            'dc_ip' => $dc->getDcIp(),
            'dc_port' => $dc->getDcPort()
        ];

        $serialized = base64_encode($authKey) . ':' . bin2hex(json_encode($metaInfo));
        return new AuthKey_v2($serialized);
    }


    /**
     * @return string
     */
    public function getRawAuthKey()
    {
        $authKey = explode(':', $this->serializedAuthKey)[0];
        return base64_decode($authKey);
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
        $meta = $this->getMetaInfo();
        if(isset($meta['dc_ip']) && isset($meta['dc_id']))
            return new DataCentre(
                $meta['dc_ip'],
                $meta['dc_id'],
                isset($meta['dc_port']) ? $meta['dc_port'] : LibConfig::DC_DEFAULT_PORT);

        return DataCentre::getDefault();
    }


    /**
     * @return array
     */
    private function getMetaInfo()
    {
        $meta = explode(':', $this->serializedAuthKey)[1];
        return json_decode(hex2bin($meta), true);
    }
}