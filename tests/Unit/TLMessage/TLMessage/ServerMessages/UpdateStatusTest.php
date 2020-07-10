<?php

declare(strict_types=1);

namespace Unit\TLMessage\TLMessage\ServerMessages;

use PHPUnit\Framework\TestCase;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Update\UpdateShort;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Update\UpdateUserStatus;

class UpdateStatusTest extends TestCase
{
    /** @noinspection PhpUnhandledExceptionInspection */
    public function test_update_status_online(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $update = new AnonymousMessageMock([
            '_'      => 'updateShort',
            'update' => [
                '_'       => 'updateUserStatus',
                'user_id' => 5001011,
                'status'  => [
                    '_'       => 'userStatusOnline',
                    'expires' => 378256982,
                ],
            ],
            'date' => 1533376561,
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $updateShort = new UpdateShort($update);
        self::assertTrue(UpdateUserStatus::isIt($updateShort->getUpdate()));
        /** @noinspection PhpUnhandledExceptionInspection */
        $updateUserStatus = new UpdateUserStatus($updateShort->getUpdate());

        self::assertEquals(5001011, $updateUserStatus->getUserId());
        self::assertTrue($updateUserStatus->getStatus()->isOnline());
        self::assertFalse($updateUserStatus->getStatus()->isOffline());
        self::assertFalse($updateUserStatus->getStatus()->isHidden());
        self::assertEquals(378256982, $updateUserStatus->getStatus()->getExpires());

    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function test_update_status_offline(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $update = new AnonymousMessageMock([
            '_'      => 'updateShort',
            'update' => [
                '_'       => 'updateUserStatus',
                'user_id' => 987436509243,
                'status'  => [
                    '_'          => 'userStatusOffline',
                    'was_online' => 784358232,
                ],
            ],
            'date' => 1533376561,
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $updateShort = new UpdateShort($update);
        self::assertTrue(UpdateUserStatus::isIt($updateShort->getUpdate()));
        /** @noinspection PhpUnhandledExceptionInspection */
        $updateUserStatus = new UpdateUserStatus($updateShort->getUpdate());

        self::assertEquals(987436509243, $updateUserStatus->getUserId());
        self::assertFalse($updateUserStatus->getStatus()->isOnline());
        self::assertTrue($updateUserStatus->getStatus()->isOffline());
        self::assertFalse($updateUserStatus->getStatus()->isHidden());
        self::assertEquals(784358232, $updateUserStatus->getStatus()->getWasOnline());
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function test_update_status_hidden(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $update = new AnonymousMessageMock([
            '_'      => 'updateShort',
            'update' => [
                '_'       => 'updateUserStatus',
                'user_id' => 50000300,
                'status'  => [
                    '_' => 'userStatusEmpty',
                ],
            ],
            'date' => 1533376561,
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $updateShort = new UpdateShort($update);
        self::assertTrue(UpdateUserStatus::isIt($updateShort->getUpdate()));
        /** @noinspection PhpUnhandledExceptionInspection */
        $updateUserStatus = new UpdateUserStatus($updateShort->getUpdate());

        self::assertEquals(50000300, $updateUserStatus->getUserId());
        self::assertFalse($updateUserStatus->getStatus()->isOnline());
        self::assertFalse($updateUserStatus->getStatus()->isOffline());
        self::assertTrue($updateUserStatus->getStatus()->isHidden());
    }
}
