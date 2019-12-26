<?php

namespace Registration\DeviceGenerator;

class DeviceResource
{
    /**
     * @var string
     */
    private $brand;
    /**
     * @var string
     */
    private $model;
    /**
     * @var int
     */
    private $sdkVersion;

    public function __construct()
    {
        $devices = json_decode(file_get_contents(__DIR__.'/devices.json'), true);
        $randomDevice = $devices[array_rand($devices)];

        $this->brand = $randomDevice['brand'];
        $this->model = $randomDevice['model'];

        $sdks = $randomDevice['sdks'];
        $this->sdkVersion = $sdks[array_rand($sdks)];
    }

    /**
     * @return string
     */
    public function getDeviceString()
    {
        return $this->brand.$this->model;
    }

    /**
     * @return string
     */
    public function getSdkString()
    {
        return 'SDK '.$this->sdkVersion;
    }
}
