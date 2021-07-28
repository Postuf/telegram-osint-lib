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
    }

    public function getDeviceString(): string
    {
        return $this->brand.$this->model;
    }

    public function getSdkString(): string
    {
        return 'SDK '.rand(self::getMinSdkVersion(), self::getMaxSdkVersion());
    }

    public static function getUpdatedSdkVersion($currentSdkString): string
    {
        $intVersion = strstr($currentSdkString, 'SDK') ?
            (int) explode(' ', $currentSdkString)[1] : (int) $currentSdkString;
        $newVersion = $intVersion < self::getMinSdkVersion() ?
            self::getMinSdkVersion() + ($intVersion % (self::getMaxSdkVersion() - self::getMinSdkVersion() + 1)) : $intVersion;

        return 'SDK '.$newVersion;
    }

    public static function getMinSdkVersion(): int
    {
        return 26;
    }

    public static function getMaxSdkVersion(): int
    {
        return 30;
    }
}
