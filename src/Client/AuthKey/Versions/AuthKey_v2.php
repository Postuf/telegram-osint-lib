<?php

namespace TelegramOSINT\Client\AuthKey\Versions;

use JsonException;
use TelegramOSINT\Client\AuthKey\AuthKey;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\LibConfig;
use TelegramOSINT\TGConnection\DataCentre;
use Throwable;

/**
 * base64(<authKey>):hex(json(<meta_info>))
 */
class AuthKey_v2 implements AuthKey
{
    private string $serializedAuthKey;

    /**
     * @param string $serializedAuthKey
     *
     * @throws TGException
     */
    public function __construct(string $serializedAuthKey)
    {
        $this->checkSerializedAuthKey($serializedAuthKey);
        $this->serializedAuthKey = $serializedAuthKey;
    }

    /**
     * @param string $authKey
     *
     * @throws TGException
     */
    private function checkSerializedAuthKey($authKey): void
    {
        try {
            $authKeyParts = explode(':', $authKey);
            if (count($authKeyParts) !== 2) {
                throw new TGException(TGException::ERR_AUTH_KEY_BAD_FORMAT);
            }
            $meta = json_decode(@hex2bin($authKeyParts[1]), true, 512, JSON_THROW_ON_ERROR);
            if (!isset($meta['created'])) {
                throw new TGException(TGException::ERR_AUTH_KEY_BAD_FORMAT);
            }
            if (strlen(base64_decode($authKeyParts[0])) !== 256) {
                throw new TGException(TGException::ERR_AUTH_KEY_BAD_FORMAT);
            }
            if (!@hex2bin($authKeyParts[1]) || !json_decode(@hex2bin($authKeyParts[1]), true, 512, JSON_THROW_ON_ERROR)) {
                throw new TGException(TGException::ERR_AUTH_KEY_BAD_FORMAT);
            }
        } catch (TGException $tge) {
            throw $tge;
        } catch (Throwable $e) {
            throw new TGException(TGException::ERR_AUTH_KEY_BAD_FORMAT);
        }
    }

    /**
     * @param string     $authKey
     * @param DataCentre $dc
     *
     * @throws TGException
     *
     * @return AuthKey_v2
     */
    public static function serialize(string $authKey, DataCentre $dc): self
    {
        $metaInfo = [
            'created' => time(),
            'api_id'  => LibConfig::APP_API_ID,
            'dc_id'   => $dc->getDcId(),
            'dc_ip'   => $dc->getDcIp(),
            'dc_port' => $dc->getDcPort(),
        ];

        try {
            $serialized = base64_encode($authKey).':'.bin2hex(json_encode($metaInfo, JSON_THROW_ON_ERROR));
        } catch (JsonException $e) {
            throw new TGException(TGException::ERR_JSON_ERROR, $e->getMessage());
        }

        return new self($serialized);
    }

    public function getRawAuthKey(): string
    {
        $authKey = explode(':', $this->serializedAuthKey, 2)[0];

        return base64_decode($authKey);
    }

    public function getSerializedAuthKey(): string
    {
        return $this->serializedAuthKey;
    }

    /**
     * @throws TGException
     *
     * @return DataCentre
     */
    public function getAttachedDC(): DataCentre
    {
        $meta = $this->getMetaInfo();
        if (isset($meta['dc_ip'], $meta['dc_id'])) {
            return new DataCentre(
                $meta['dc_ip'],
                $meta['dc_id'],
                $meta['dc_port'] ?? LibConfig::DC_DEFAULT_PORT
            );
        }

        return DataCentre::getDefault();
    }

    /**
     * @throws TGException
     *
     * @return array
     */
    private function getMetaInfo(): ?array
    {
        $meta = explode(':', $this->serializedAuthKey)[1];

        try {
            return (array) json_decode(hex2bin($meta), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new TGException(TGException::ERR_JSON_ERROR, $e->getMessage());
        }
    }
}
