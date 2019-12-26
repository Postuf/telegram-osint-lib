<?php

namespace Tests\Tests\Registration;

use PHPUnit\Framework\TestCase;
use Registration\DeviceGenerator\DeviceResource;
use Registration\NameGenerator\NameResource;

class GeneratorTest extends TestCase
{
    public function test_human_name_generation()
    {
        $generator = new NameResource();
        $this->assertTrue(strlen($generator->getName()) > 0);
        $this->assertTrue(strlen($generator->getLastName()) > 0);
    }

    public function test_device_generation()
    {
        $generator = new DeviceResource();
        $this->assertTrue(strpos($generator->getSdkString(), 'SDK') === 0);
        $this->assertTrue(strlen($generator->getDeviceString()) > 0);
    }
}
