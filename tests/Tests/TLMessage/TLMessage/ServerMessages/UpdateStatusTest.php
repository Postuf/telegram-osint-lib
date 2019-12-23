<?php

namespace Tests\Tests\TLMessage\TLMessage\ServerMessages;

use PHPUnit\Framework\TestCase;
use TLMessage\TLMessage\ServerMessages\Update\UpdateShort;
use TLMessage\TLMessage\ServerMessages\Update\UpdateUserStatus;

class UpdateStatusTest extends TestCase
{

    public function test_update_status_online()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $update = new AnonymousMessageMock([
            '_' => 'updateShort',
            'update' => [
                '_' => 'updateUserStatus',
                'user_id' => 5001011,
                'status' => [
                    '_' => 'userStatusOnline',
                    'expires' => 378256982
                ]
            ],
            'date' => 1533376561
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $updateShort = new UpdateShort($update);
        self::assertTrue(UpdateUserStatus::isIt($updateShort->getUpdate()));
        /** @noinspection PhpUnhandledExceptionInspection */
        $updateUserStatus = new UpdateUserStatus($updateShort->getUpdate());

        $this->assertEquals($updateUserStatus->getUserId(), 5001011);
        $this->assertTrue($updateUserStatus->getStatus()->isOnline());
        $this->assertFalse($updateUserStatus->getStatus()->isOffline());
        $this->assertFalse($updateUserStatus->getStatus()->isHidden());
        $this->assertEquals($updateUserStatus->getStatus()->getExpires(), 378256982);

    }


    public function test_update_status_offline()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $update = new AnonymousMessageMock([
            '_' => 'updateShort',
            'update' => [
                '_' => 'updateUserStatus',
                'user_id' => 987436509243,
                'status' => [
                    '_' => 'userStatusOffline',
                    'was_online' => 784358232
                ]
            ],
            'date' => 1533376561
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $updateShort = new UpdateShort($update);
        self::assertTrue(UpdateUserStatus::isIt($updateShort->getUpdate()));
        /** @noinspection PhpUnhandledExceptionInspection */
        $updateUserStatus = new UpdateUserStatus($updateShort->getUpdate());

        $this->assertEquals($updateUserStatus->getUserId(), 987436509243);
        $this->assertFalse($updateUserStatus->getStatus()->isOnline());
        $this->assertTrue($updateUserStatus->getStatus()->isOffline());
        $this->assertFalse($updateUserStatus->getStatus()->isHidden());
        $this->assertEquals($updateUserStatus->getStatus()->getWasOnline(), 784358232);
    }


    public function test_update_status_hidden()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $update = new AnonymousMessageMock([
            '_' => 'updateShort',
            'update' => [
                '_' => 'updateUserStatus',
                'user_id' => 50000300,
                'status' => [
                    '_' => 'userStatusEmpty'
                ]
            ],
            'date' => 1533376561
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $updateShort = new UpdateShort($update);
        self::assertTrue(UpdateUserStatus::isIt($updateShort->getUpdate()));
        /** @noinspection PhpUnhandledExceptionInspection */
        $updateUserStatus = new UpdateUserStatus($updateShort->getUpdate());

        $this->assertEquals($updateUserStatus->getUserId(), 50000300);
        $this->assertFalse($updateUserStatus->getStatus()->isOnline());
        $this->assertFalse($updateUserStatus->getStatus()->isOffline());
        $this->assertTrue($updateUserStatus->getStatus()->isHidden());
    }
}
