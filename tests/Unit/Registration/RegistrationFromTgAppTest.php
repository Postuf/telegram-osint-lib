<?php

declare(strict_types=1);

namespace Unit\Registration;

use PHPUnit\Framework\TestCase;
use TelegramOSINT\Registration\RegistrationFromTgApp;

class RegistrationFromTgAppTest extends TestCase
{
    public function test_construct(): void
    {
        $reg = new RegistrationFromTgApp();
        $this->assertInstanceOf(RegistrationFromTgApp::class, $reg);
    }
}
