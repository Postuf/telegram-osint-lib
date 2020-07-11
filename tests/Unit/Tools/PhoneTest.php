<?php

declare(strict_types=1);

namespace Unit\Tools;

use PHPUnit\Framework\TestCase;
use TelegramOSINT\Tools\Phone;

class PhoneTest extends TestCase
{
    public function test_username_equals(): void
    {
        $ph1 = '123553';
        $ph2 = '123553';
        self::assertEquals(true, Phone::equal($ph1, $ph2));
    }

    public function test_username_not_equals(): void
    {
        $ph1 = '123553';
        $ph2 = '123554';
        self::assertEquals(false, Phone::equal($ph1, $ph2));
    }
}
