<?php

declare(strict_types=1);

namespace Unit\Registration;

use PHPUnit\Framework\TestCase;
use TelegramOSINT\Registration\DeviceGenerator\DeviceResource;
use TelegramOSINT\Registration\NameGenerator\NameResource;

class GeneratorTest extends TestCase
{
    public function test_human_name_generation(): void
    {
        $generator = new NameResource();
        self::assertNotSame($generator->getName(), '');
        self::assertNotSame($generator->getLastName(), '');
    }

    public function test_device_generation(): void
    {
        $generator = new DeviceResource();
        self::assertSame(strpos($generator->getSdkString(), 'SDK'), 0);
        self::assertNotSame($generator->getDeviceString(), '');
    }

    public function test_android_version_substitution(): void
    {
        self::assertSame('SDK 27', DeviceResource::getUpdatedSdkVersion('SDK 21'));
        self::assertSame('SDK 30', DeviceResource::getUpdatedSdkVersion('SDK 30'));
        self::assertSame('SDK 26', DeviceResource::getUpdatedSdkVersion('SDK 20'));
    }
}
