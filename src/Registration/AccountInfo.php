<?php

namespace Registration;

use Exception\TGException;
use LibConfig;
use Registration\DeviceGenerator\DeviceResource;
use Registration\NameGenerator\NameResource;

class AccountInfo
{
    /** @var string */
    private $device;
    /** @var string */
    private $androidSdkVersion;

    /** @var string */
    private $firstName;
    /** @var string */
    private $lastName;

    /** @var string */
    private $deviceLang;
    /** @var string */
    private $appLang;

    /** @var string */
    private $appVersion;
    /** @var string */
    private $appVersionCode;
    /** @var int */
    private $layerVersion;

    private function __construct()
    {

    }

    /**
     * @return AccountInfo
     */
    public static function generate()
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

    /**
     * @return string
     */
    public function serializeToJson()
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

        return json_encode($bundle);
    }

    /**
     * @param string $serialized
     *
     * @throws TGException
     *
     * @return AccountInfo
     */
    public static function deserializeFromJson(string $serialized)
    {
        $bundle = json_decode($serialized, true);

        if(!$bundle)
            throw new TGException(TGException::ERR_AUTH_KEY_BAD_ACCOUNT_INFO);
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

    /**
     * @return string
     */
    public function getDevice()
    {
        return $this->device;
    }

    /**
     * @return string
     */
    public function getAndroidSdkVersion()
    {
        return $this->androidSdkVersion;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getDeviceLang()
    {
        return $this->deviceLang;
    }

    /**
     * @return string
     */
    public function getAppLang()
    {
        return $this->appLang;
    }

    /**
     * @return string
     */
    public function getAppVersion()
    {
        return $this->appVersion;
    }

    /**
     * @return string
     */
    public function getAppVersionCode()
    {
        return $this->appVersionCode;
    }

    /**
     * @return int
     */
    public function getLayerVersion()
    {
        return $this->layerVersion;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }
}
