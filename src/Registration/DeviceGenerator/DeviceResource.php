<?php

/** @noinspection NonSecureArrayRandUsageInspection */

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
            /** @noinspection PhpUnhandledExceptionInspection */
            self::$devices = json_decode(file_get_contents(__DIR__.'/devices.json'), true, 512, JSON_THROW_ON_ERROR);
        }
        $randomDevice = self::$devices[array_rand(self::$devices)];

        $this->brand = $randomDevice['brand'];
        $this->model = $randomDevice['model'];

        $sdks = $randomDevice['sdks'];
        $this->sdkVersion = $sdks[array_rand($sdks)];
    }

    public function getDeviceString(): string
    {
        return $this->brand.$this->model;
    }

    public function getSdkString(): string
    {
        return 'SDK '.$this->sdkVersion;
    }
}
