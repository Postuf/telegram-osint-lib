<?php

declare(strict_types=1);

namespace Unit\TLMessage\TLMessage\ClientMessages;

use PHPUnit\Framework\TestCase;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\get_config;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\get_contacts;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\get_langpack;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\get_languages;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\get_terms_of_service_update;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\invoke_with_layer;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\p_q_inner_data_dc;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\reset_saved_contacts;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\send_sms_code;

class TgAppSerializationTest extends TestCase
{
    public function test_get_contacts_serialization(): void
    {
        self::assertEquals(
            (new get_contacts())->toBinary(),
            hex2bin('129ed65d0000000000000000')
        );
    }

    public function test_get_langpack_serialization(): void
    {
        self::assertEquals(
            (new get_langpack('ru'))->toBinary(),
            hex2bin('0a33f2f207616e64726f696402727500')
        );
    }

    public function test_get_languages_serialization(): void
    {
        self::assertEquals(
            (new get_languages())->toBinary(),
            hex2bin('8f97c64207616e64726f6964')
        );
    }

    public function test_get_tos_serialization(): void
    {
        self::assertEquals(
            (new get_terms_of_service_update())->toBinary(),
            hex2bin('d11fa52c')
        );
    }

    public function test_invoke_with_layer_serialization(): void
    {
        self::assertEquals(
            '0d0d9bda520000006b18f9c4',
            bin2hex((new invoke_with_layer(82, new get_config()))->toBinary())
        );
    }

    public function test_p_q_inner_data_dc_serialization(): void
    {
        self::assertEquals(
            (new p_q_inner_data_dc((int) '1550767997241791113', 1033421369, (int) '1500615377', 'erwterterwt', 'retwertewt', 'retrtewtewr', 2))->toBinary(),
            hex2bin('955ff5a90815856f46f4a41289000000043d98c23900000004597192d1000000657277746572746572777472657477657274657774726574727465777465777202000000')
        );
    }

    public function test_input_file_location_serialization(): void
    {
        self::assertEquals(
            'f1379587',
            bin2hex((new reset_saved_contacts())->toBinary())
        );
    }

    public function test_send_sms_code_serialization(): void
    {
        self::assertStringStartsWith(
            '4f2477a60a33323533323435333432000400000020303134623335623631383431303062303835623064303537326639623531303300000083bebede10000000',
            bin2hex((new send_sms_code('3253245342'))->toBinary())
        );
    }
}
