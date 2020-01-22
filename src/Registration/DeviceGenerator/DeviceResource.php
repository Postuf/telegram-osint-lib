<?php

declare(strict_types=1);

namespace TelegramOSINT\Registration\DeviceGenerator;

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
    /** @var array */
    private static $devices = [];

    public function __construct()
    {
        if (!self::$devices) {
            self::$devices = json_decode(file_get_contents(__DIR__.'/devices.json'), true);
        }
        $randomDevice = self::$devices[array_rand(self::$devices)];

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
