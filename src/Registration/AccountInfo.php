<?php

namespace TelegramOSINT\Registration;

use JsonException;
use TelegramOSINT\Client\AuthKey\AuthorizedAuthKey;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\LibConfig;
use TelegramOSINT\Registration\DeviceGenerator\DeviceResource;
use TelegramOSINT\Registration\NameGenerator\NameResource;

class AccountInfo
{
    /** @var string */
    private string $device;
    /** @var string */
    private string $androidSdkVersion;

    /** @var string */
    private string $firstName;
    /** @var string */
    private string $lastName;

    /** @var string */
    private string $deviceLang;
    /** @var string */
    private string $appLang;

    /** @var string */
    private string $appVersion;
    /** @var string */
    private string $appVersionCode;
    /** @var int */
    private int $layerVersion;

    private function __construct()
    {
    }

    /**
     * @return AccountInfo
     */
    public static function generate(): self
    {
        $acc = new self();

        $device = new DeviceResource();
        $acc->device = $device->getDeviceString();
        $acc->androidSdkVersion = $device->getSdkString();
        unset($device);

        $human = new NameResource();
        $acc->firstName = $human->getName();
        $acc->lastName = $human->getLastName();
        unset($humanName);

        $acc->deviceLang = LibConfig::APP_DEFAULT_DEVICE_LANG_CODE;
        $acc->appLang = LibConfig::APP_DEFAULT_LANG_CODE;
        $acc->appVersion = LibConfig::APP_DEFAULT_VERSION;
        $acc->appVersionCode = LibConfig::APP_DEFAULT_VERSION_CODE;
        $acc->layerVersion = LibConfig::APP_DEFAULT_TL_LAYER_VERSION;

        return $acc;
    }

    public static function generateFromAuthKey(AuthorizedAuthKey $authKey): self
    {
        $base = self::generate();
        $base->device = $authKey->getAccountInfo()->getDevice();
        $base->firstName = $authKey->getAccountInfo()->firstName;
        $base->lastName = $authKey->getAccountInfo()->lastName;
        return $base;
    }

    /**
     * @return string
     */
    public function serializeToJson(): string
    {
        $bundle = [];
        $bundle['device'] = $this->device;
        $bundle['androidSdkVersion'] = $this->androidSdkVersion;
        $bundle['firstName'] = $this->firstName;
        $bundle['lastName'] = $this->lastName;
        $bundle['deviceLang'] = $this->deviceLang;
        $bundle['appLang'] = $this->appLang;
        $bundle['appVersion'] = $this->appVersion;
        $bundle['appVersionCode'] = $this->appVersionCode;
        $bundle['layerVersion'] = $this->layerVersion;

        try {
            return json_encode($bundle, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return '{}';
        }
    }

    /**
     * @param string $serialized
     *
     * @throws TGException
     *
     * @return AccountInfo
     */
    public static function deserializeFromJson(string $serialized): self
    {
        try {
            $bundle = json_decode($serialized, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new TGException(TGException::ERR_AUTH_KEY_BAD_ACCOUNT_INFO);
        }

        if (!$bundle) {
            throw new TGException(TGException::ERR_AUTH_KEY_BAD_ACCOUNT_INFO);
        }
        $accountInfo = new self();
        $accountInfo->device = $bundle['device'];
        $accountInfo->androidSdkVersion = $bundle['androidSdkVersion'];
        $accountInfo->firstName = $bundle['firstName'];
        $accountInfo->lastName = $bundle['lastName'];
        $accountInfo->deviceLang = $bundle['deviceLang'];
        $accountInfo->appLang = $bundle['appLang'];
        $accountInfo->appVersion = $bundle['appVersion'];
        $accountInfo->appVersionCode = $bundle['appVersionCode'];
        $accountInfo->layerVersion = $bundle['layerVersion'];

        return $accountInfo;
    }

    public function getDevice(): string
    {
        return $this->device;
    }

    public function getAndroidSdkVersion(): string
    {
        return DeviceResource::getUpdatedSdkVersion($this->androidSdkVersion);
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getDeviceLang(): string
    {
        return $this->deviceLang;
    }

    public function getAppLang(): string
    {
        return $this->appLang;
    }

    public function getAppVersion(): string
    {
        return $this->appVersion;
    }

    public function getAppVersionCode(): string
    {
        return $this->appVersionCode;
    }

    public function getLayerVersion(): int
    {
        return $this->layerVersion;
    }

    /**
     * @param string $firstName
     * @noinspection PhpUnused
     * @noinspection UnknownInspectionInspection
     */
    public function setFirstName($firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @param string $lastName
     * @noinspection PhpUnused
     * @noinspection UnknownInspectionInspection
     */
    public function setLastName($lastName): void
    {
        $this->lastName = $lastName;
    }
}
