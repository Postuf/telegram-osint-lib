<?php

declare(strict_types=1);

namespace Unit\TLMessage\TLMessage\ServerMessages;

use PHPUnit\Framework\TestCase;
use TelegramOSINT\MTSerialization\OwnImplementation\OwnAnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Auth\DHServerInnerData;

class DHServerInnerDataTest extends TestCase
{
    /** @noinspection PhpUnhandledExceptionInspection */
    public function test_getG(): void
    {
        $anonymousMessage = new OwnAnonymousMessage([
            '_'            => 'server_DH_inner_data',
            'nonce'        => hex2bin('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'),
            'server_nonce' => hex2bin('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'),
            'g'            => 123,
            'dh_prime'     => 'bytes',
            'g_a'          => 'bytes',
            'server_time'  => time(),
        ]);
        $dhData = new DHServerInnerData($anonymousMessage);
        $expectedG = bin2hex($dhData->getG());
        self::assertEquals('0000007b', $expectedG);
    }
}
