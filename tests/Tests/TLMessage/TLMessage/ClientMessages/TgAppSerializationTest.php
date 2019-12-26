<?php

namespace Tests\Tests\TLMessage\TLMessage\ClientMessages;

use PHPUnit\Framework\TestCase;
use TLMessage\TLMessage\ClientMessages\Shared\get_config;
use TLMessage\TLMessage\ClientMessages\TgApp\get_contacts;
use TLMessage\TLMessage\ClientMessages\TgApp\get_langpack;
use TLMessage\TLMessage\ClientMessages\TgApp\get_languages;
use TLMessage\TLMessage\ClientMessages\TgApp\get_terms_of_service_update;
use TLMessage\TLMessage\ClientMessages\TgApp\invoke_with_layer;
use TLMessage\TLMessage\ClientMessages\TgApp\p_q_inner_data_dc;
use TLMessage\TLMessage\ClientMessages\TgApp\reset_saved_contacts;
use TLMessage\TLMessage\ClientMessages\TgApp\send_sms_code;

class TgAppSerializationTest extends TestCase
{
    public function test_get_contacts_serialization()
    {
        $this->assertEquals(
            (new get_contacts())->toBinary(),
            hex2bin('9f8423c000000000')
        );
    }

    public function test_get_langpack_serialization()
    {
        $this->assertEquals(
            (new get_langpack('ru'))->toBinary(),
            hex2bin('8ec5b59a02727500')
        );
    }

    public function test_get_languages_serialization()
    {
        $this->assertEquals(
            (new get_languages())->toBinary(),
            hex2bin('7dd50f80')
        );
    }

    public function test_get_tos_serialization()
    {
        $this->assertEquals(
            (new get_terms_of_service_update())->toBinary(),
            hex2bin('d11fa52c')
        );
    }

    public function test_invoke_with_layer_serialization()
    {
        $this->assertEquals(
            bin2hex((new invoke_with_layer(82, new get_config()))->toBinary()),
            '0d0d9bda520000006b18f9c4'
        );
    }

    public function test_p_q_inner_data_dc_serialization()
    {
        $this->assertEquals(
            (new p_q_inner_data_dc('1550767997241791113', 1033421369, '1500615377', 'erwterterwt', 'retwertewt', 'retrtewtewr', 2))->toBinary(),
            hex2bin('955ff5a90815856f46f4a41289000000043d98c23900000004597192d1000000657277746572746572777472657477657274657774726574727465777465777202000000')
        );
    }

    public function test_input_file_location_serialization()
    {
        $this->assertEquals(
            bin2hex((new reset_saved_contacts())->toBinary()),
            'f1379587'
        );
    }

    public function test_send_sms_code_serialization()
    {
        $this->assertStringStartsWith(
            '4f2477a60a33323533323435333432000600000020656230366434616266623439646333656562316165623938616530663538316500000083bebede10000000',
            bin2hex((new send_sms_code('3253245342'))->toBinary())
        );
    }
}
