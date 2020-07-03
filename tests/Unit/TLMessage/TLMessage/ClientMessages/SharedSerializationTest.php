<?php

/** @noinspection SpellCheckingInspection */
declare(strict_types=1);

namespace Unit\TLMessage\TLMessage\ClientMessages;

use PHPUnit\Framework\TestCase;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\client_dh_inner_data;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\delete_contacts;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\export_authorization;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\get_config;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\get_file;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\get_statuses;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\import_authorization;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\input_file_location;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\msgs_ack;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\p_q_inner_data;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\ping;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\req_dh_params;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\req_pq_multi;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\set_client_dh_params;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\sign_in;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\sign_up;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\update_status;

class SharedSerializationTest extends TestCase
{
    public function test_client_dh_inner_data_serialization(): void
    {
        $this->assertEquals(
            (new client_dh_inner_data('AAAAAAAA', 'BBBBBBBB', 0, 'euighpsdfuhgpeirtheihepeirterptuheprut'))->toBinary(),
            hex2bin('54b6436641414141414141414242424242424242000000000000000026657569676870736466756867706569727468656968657065697274657270747568657072757400')
        );
    }

    public function test_delete_contacts_serialization(): void
    {
        $delContacts = new delete_contacts();
        $delContacts->addToDelete(34576439852, 34534);
        $delContacts->addToDelete(94365239753, 89734);
        $delContacts->addToDelete(63532984728, 78632);

        $this->assertEquals(
            $delContacts->toBinary(),
            hex2bin('000e6a0915c4b51c03000000162829d8e68600002c9aea0c08000000162829d8865e0100c9499bf815000000162829d8283301009869dcca0e000000')
        );
    }

    public function test_export_authorization_serialization(): void
    {
        $this->assertEquals(
            (new export_authorization(4))->toBinary(),
            hex2bin('cdffbfe504000000')
        );
    }

    public function test_get_config_serialization(): void
    {
        $this->assertEquals(
            (new get_config())->toBinary(),
            hex2bin('6b18f9c4')
        );
    }

    public function test_get_file_serialization(): void
    {
        $this->assertEquals(
            (new get_file(new input_file_location(23423423, 23534534, 345345345, ''), 5096, 4096))->toBinary(),
            hex2bin('fc9a5ab101000000e1abdadfbf69650100000000c61b6701418d95140000000000000000e813000000100000')
        );
    }

    public function test_get_statuses_serialization(): void
    {
        $this->assertEquals(
            'ee53a3c4',
            bin2hex((new get_statuses())->toBinary())
        );
    }

    public function test_import_authorization_serialization(): void
    {
        $this->assertEquals(
            (new import_authorization(44234234, 'rewrwerewrrwerq34231'))->toBinary(),
            hex2bin('1396efe3faf5a202147265777277657265777272776572713334323331000000')
        );
    }

    public function test_input_file_location_serialization(): void
    {
        $this->assertEquals(
            (new input_file_location(4353452345, 34532453245, 3453453245, ''))->toBinary(),
            hex2bin('e1abdadf39697c03010000007d6b4b0abd83d7cd0000000000000000')
        );
    }

    public function test_msgs_ack_serialization(): void
    {
        $this->assertEquals(
            (new msgs_ack([325134, 43532, 5345, 34, 524, 53, 4532453245, 3245, 23]))->toBinary(),
            hex2bin('59b4d66215c4b51c090000000ef60400000000000caa000000000000e11400000000000022000000000000000c0200000000000035000000000000007dbf270e01000000ad0c0000000000001700000000000000')
        );
    }

    public function test_p_q_inner_data_serialization(): void
    {
        $this->assertEquals(
            (new p_q_inner_data(1550767997241791113, 1033421369, 1500615377, 'ergrterte45543645', '5424yrthfdgyt56udfd', '34543twgfd'))->toBinary(),
            hex2bin('ec5ac9830815856f46f4a41289000000043d98c23900000004597192d100000065726772746572746534353534333634353534323479727468666467797435367564666433343534337477676664')
        );
    }

    public function test_ping_serialization(): void
    {
        $this->assertEquals(
            'ec77be7a4141414141414141',
            bin2hex((new ping('AAAAAAAA'))->toBinary())
        );
    }

    public function test_req_dh_params_serialization(): void
    {
        $this->assertEquals(
            (new req_dh_params('wqerwer23rfds', '5trwsdfr34tgw', 2342134, 345324, 34534253, '34523453532dfg'))->toBinary(),
            hex2bin('bee412d77771657277657232337266647335747277736466723334746777040023bcf600000004000544ec0000006df30e02000000000e333435323334353335333264666700')
        );
    }

    public function test_req_pq_multi_serialization(): void
    {
        $this->assertEquals(
            'f18e7ebe3435373874776773726875666c79743834',
            bin2hex((new req_pq_multi('4578twgsrhuflyt84'))->toBinary())
        );
    }

    public function test_set_client_dh_params_serialization(): void
    {
        $this->assertEquals(
            '1f5f04f5383574776768736f3b666968676468206f383237677774736668647567386f7765113233377477677368666467686f656772650000',
            bin2hex((new set_client_dh_params('85twghso;fihgdh', ' o827gwtsfhdug8owe', '237twgshfdghoegre'))->toBinary())
        );
    }

    public function test_sign_in_serialization(): void
    {
        $this->assertEquals(
            '8115d5bc09393338343332393035000011323334373935747968737075676872657700000533343533340000',
            bin2hex((new sign_in('938432905', '234795tyhspughrew', '34534'))->toBinary())
        );
    }

    public function test_sign_up_serialization(): void
    {
        $this->assertEquals(
            '27e4ee80093933383433323930350000113233343739357479687370756768726577000005333435333400000665727472657400',
            bin2hex((new sign_up('938432905', '234795tyhspughrew', '34534', 'ertret'))->toBinary())
        );
    }

    public function test_update_status_serialization(): void
    {
        $this->assertEquals(
            '2c562866379779bc',
            bin2hex((new update_status(true))->toBinary())
        );

        $this->assertEquals(
            '2c562866b5757299',
            bin2hex((new update_status(false))->toBinary())
        );
    }
}
