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
}
