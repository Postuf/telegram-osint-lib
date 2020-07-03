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
        $this->assertNotSame($generator->getName(), '');
        $this->assertNotSame($generator->getLastName(), '');
    }

    public function test_device_generation(): void
    {
        $generator = new DeviceResource();
        $this->assertSame(strpos($generator->getSdkString(), 'SDK'), 0);
        $this->assertNotSame($generator->getDeviceString(), '');
    }
}
