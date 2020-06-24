<?php

declare(strict_types=1);

namespace Unit\Client\Helpers;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TelegramOSINT\Client\ContactKeepingClient;
use TelegramOSINT\Client\Helpers\ReloadContactsHandler;
use TelegramOSINT\Client\StatusWatcherClient\Models\ImportResult;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Contact\ContactUser;
use Unit\Client\StatusWatcherClient\AnonymousMessageMock;

class ReloadContactsHandlerTest extends TestCase
{
    /** @var MockObject|ContactKeepingClient */
    private $keeperMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->keeperMock = $this->createMock(ContactKeepingClient::class);
    }

    /**
     * Test that we add numbers if no contacts are imported
     *
     * @noinspection PhpUnusedParameterInspection
     */
    public function test_reload_numbers_empty(): void
    {
        $complete = false;
        $cb = static function (ImportResult $result) use (&$complete) {
            $complete = true;
        };
        $handler = ReloadContactsHandler::getHandler($this->keeperMock, ['123'], [], $cb);
        $this->keeperMock
            ->expects($this->once())
            ->method('addNumbers')
            ->willReturnCallback(function (array $numbers, callable $onComplete) {
                $this->assertEquals(['123'], $numbers);
                $onComplete(new ImportResult());
            });

        $this->keeperMock
            ->expects($this->never())
            ->method('delNumbers');

        // we get empty contacts imported
        $handler([]);
        $this->assertTrue($complete);
    }

    /**
     * Test that we remove numbers if they are not in list
     *
     * @noinspection PhpUnusedParameterInspection
     */
    public function test_reload_numbers_del(): void
    {
        $complete = false;
        $cb = static function (ImportResult $result) use (&$complete) {
            $complete = true;
        };
        $handler = ReloadContactsHandler::getHandler($this->keeperMock, [], [], $cb);
        $this->keeperMock
            ->expects($this->never())
            ->method('addNumbers');

        $this->keeperMock
            ->expects($this->once())
            ->method('delNumbers')
            ->willReturnCallback(static function (array $numbers, callable $onComplete) {
                $onComplete(new ImportResult());
            });

        // we get empty contacts imported
        /** @noinspection PhpUnhandledExceptionInspection */
        $handler([$this->createContact('456')]);
        $this->assertTrue($complete);
    }

    /**
     * Test that we do not add numbers if they exist already
     *
     * @noinspection PhpUnusedParameterInspection
     */
    public function test_reload_numbers_existing(): void
    {
        $complete = false;
        $cb = static function (ImportResult $result) use (&$complete) {
            $complete = true;
        };
        $handler = ReloadContactsHandler::getHandler($this->keeperMock, ['123'], [], $cb);
        $this->keeperMock
            ->expects($this->never())
            ->method('addNumbers');

        $this->keeperMock
            ->expects($this->never())
            ->method('delNumbers');

        // we get contacts already imported
        /** @noinspection PhpUnhandledExceptionInspection */
        $handler([$this->createContact('123')]);
        $this->assertTrue($complete);
    }

    /**
     * Test that we skip number if it belongs to username
     *
     * @noinspection PhpUnusedParameterInspection
     */
    public function test_reload_numbers_existing_username(): void
    {
        $complete = false;
        $cb = static function (ImportResult $result) use (&$complete) {
            $complete = true;
        };
        $handler = ReloadContactsHandler::getHandler($this->keeperMock, ['123'], ['aaa'], $cb);
        $this->keeperMock
            ->expects($this->never())
            ->method('addNumbers');

        $this->keeperMock
            ->expects($this->never())
            ->method('delNumbers');

        // we get contacts already imported
        /** @noinspection PhpUnhandledExceptionInspection */
        $handler([
            $this->createContact('123'),
            $this->createContact('456', 'aaa'),
        ]);
        $this->assertTrue($complete);
    }

    /**
     * Test that we skip number if it belongs to username
     *
     * @noinspection PhpUnusedParameterInspection
     */
    public function test_reload_numbers_with_username(): void
    {
        $complete = false;
        $cb = static function (ImportResult $result) use (&$complete) {
            $complete = true;
        };
        $handler = ReloadContactsHandler::getHandler($this->keeperMock, ['123'], ['aaa'], $cb);
        $this->keeperMock
            ->expects($this->never())
            ->method('addNumbers');

        $this->keeperMock
            ->expects($this->never())
            ->method('delNumbers');

        // we get contacts already imported
        /** @noinspection PhpUnhandledExceptionInspection */
        $handler([
            $this->createContact('123', 'aaa'),
        ]);
        $this->assertTrue($complete);
    }

    /**
     * Test that we skip number if it belongs to username and phone is not in list
     *
     * @noinspection PhpUnusedParameterInspection
     */
    public function test_reload_numbers_with_username_without_phone(): void
    {
        $complete = false;
        $cb = static function (ImportResult $result) use (&$complete) {
            $complete = true;
        };
        $handler = ReloadContactsHandler::getHandler($this->keeperMock, [], ['aaa'], $cb);
        $this->keeperMock
            ->expects($this->never())
            ->method('addNumbers');

        $this->keeperMock
            ->expects($this->never())
            ->method('delNumbers');

        // we get contacts already imported
        /** @noinspection PhpUnhandledExceptionInspection */
        $handler([
            $this->createContact('123', 'aaa'),
        ]);
        $this->assertTrue($complete);
    }

    /**
     * @param string $phone
     * @param string $username
     *
     * @throws TGException
     *
     * @return ContactUser
     */
    public function createContact(string $phone, string $username = ''): ContactUser
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return new ContactUser(new AnonymousMessageMock([
            '_'           => 'user',
            'id'          => random_int(0, 100000),
            'access_hash' => 1,
            'phone'       => $phone,
            'username'    => $username,
        ]));
    }
}
