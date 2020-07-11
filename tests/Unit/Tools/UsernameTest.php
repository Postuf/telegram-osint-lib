<?php

declare(strict_types=1);

namespace Unit\Tools;

use PHPUnit\Framework\TestCase;
use TelegramOSINT\Tools\Username;

class UsernameTest extends TestCase
{
    public function test_username_equals(): void
    {
        $un1 = 'xxx';
        $un2 = 'xxx ';
        self::assertEquals(true, Username::equal($un1, $un2));
    }

    public function test_username_not_equals(): void
    {
        $un1 = 'xxx';
        $un2 = 'xxy ';
        self::assertEquals(false, Username::equal($un1, $un2));
    }
}
