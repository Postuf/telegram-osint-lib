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
    private const METHOD_DEL_NUMBERS_AND_USERS = 'delNumbersAndUsers';
    private const METHOD_ADD_NUMBERS = 'addNumbers';
    private const METHOD_ADD_USER = 'addUser';
    /** @var MockObject|ContactKeepingClient */
    private $keeperMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->keeperMock = $this->createMock(ContactKeepingClient::class);
    }

    /**
     * Test that we add numbers if no contacts are currently imported
     *
     * @noinspection PhpUnusedParameterInspection
     */
    public function test_reload_numbers_add_phone(): void
    {
        $complete = false;
        $cb = static function (ImportResult $result) use (&$complete) {
            $complete = true;
        };
        $handler = ReloadContactsHandler::getHandler($this->keeperMock, ['123'], [], $cb);
        $this->keeperMock
            ->expects($this->once())
            ->method(self::METHOD_ADD_NUMBERS)
            ->willReturnCallback(function (array $numbers, callable $onComplete) {
                $this->assertEquals(['123'], $numbers);
                $onComplete(new ImportResult());
            });

        $this->keeperMock
            ->expects($this->never())
            ->method(self::METHOD_DEL_NUMBERS_AND_USERS);

        // we get empty contacts imported
        $handler([]);
        $this->assertTrue($complete);
    }

    /**
     * Test that we add username if no contacts are currently imported
     *
     * @noinspection PhpUnusedParameterInspection
     */
    public function test_reload_numbers_add_user(): void
    {
        $complete = false;
        $cb = static function (ImportResult $result) use (&$complete) {
            $complete = true;
        };
        $handler = ReloadContactsHandler::getHandler($this->keeperMock, [], ['aaa'], $cb);
        $this->keeperMock
            ->expects($this->once())
            ->method(self::METHOD_ADD_USER)
            ->willReturnCallback(function (string $username, callable $onComplete) {
                $this->assertEquals('aaa', $username);
                $onComplete(true);
            });

        $this->keeperMock
            ->expects($this->never())
            ->method(self::METHOD_DEL_NUMBERS_AND_USERS);

        // we get empty contacts imported
        $handler([]);
        $this->assertTrue($complete);
    }

    /**
     * Test that we add username and number if no contacts are currently imported
     *
     * @noinspection PhpUnusedParameterInspection
     */
    public function test_reload_numbers_add_user_and_phone(): void
    {
        $complete = false;
        $cb = static function (ImportResult $result) use (&$complete) {
            $complete = true;
        };
        $handler = ReloadContactsHandler::getHandler($this->keeperMock, ['123'], ['aaa'], $cb);
        $this->keeperMock
            ->expects($this->once())
            ->method(self::METHOD_ADD_NUMBERS)
            ->willReturnCallback(function (array $numbers, callable $onComplete) {
                $this->assertEquals(['123'], $numbers);
                $onComplete(new ImportResult());
            });

        $this->keeperMock
            ->expects($this->once())
            ->method(self::METHOD_ADD_USER)
            ->willReturnCallback(function (string $username, callable $onComplete) {
                $this->assertEquals('aaa', $username);
                $onComplete(true);
            });

        $this->keeperMock
            ->expects($this->never())
            ->method(self::METHOD_DEL_NUMBERS_AND_USERS);

        // we get empty contacts imported
        $handler([]);
        $this->assertTrue($complete);
    }

    /**
     * Test that we remove numbers if they are not in list
     *
     * @noinspection PhpUnusedParameterInspection
     */
    public function test_reload_numbers_del_number(): void
    {
        $complete = false;
        $cb = static function (ImportResult $result) use (&$complete) {
            $complete = true;
        };
        $handler = ReloadContactsHandler::getHandler($this->keeperMock, [], [], $cb);
        $this->keeperMock
            ->expects($this->never())
            ->method(self::METHOD_ADD_NUMBERS);

        $this->keeperMock
            ->expects($this->once())
            ->method(self::METHOD_DEL_NUMBERS_AND_USERS)
            ->willReturnCallback(static function (array $numbers, array $users, callable $onComplete) {
                $onComplete(new ImportResult());
            });

        // we get empty contacts imported
        /** @noinspection PhpUnhandledExceptionInspection */
        $handler([$this->createContact('456')]);
        $this->assertTrue($complete);
    }

    /**
     * Test that we remove usernames if they are not in list
     *
     * @noinspection PhpUnusedParameterInspection
     */
    public function test_reload_numbers_del_username(): void
    {
        $complete = false;
        $cb = static function (ImportResult $result) use (&$complete) {
            $complete = true;
        };
        $handler = ReloadContactsHandler::getHandler($this->keeperMock, [], [], $cb);
        $this->keeperMock
            ->expects($this->never())
            ->method(self::METHOD_ADD_NUMBERS);

        $this->keeperMock
            ->expects($this->once())
            ->method(self::METHOD_DEL_NUMBERS_AND_USERS)
            ->willReturnCallback(static function (array $numbers, array $users, callable $onComplete) {
                $onComplete(new ImportResult());
            });

        // we get empty contacts imported
        /** @noinspection PhpUnhandledExceptionInspection */
        $handler([$this->createContact('', 'ccc')]);
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
            ->method(self::METHOD_ADD_NUMBERS);

        $this->keeperMock
            ->expects($this->never())
            ->method(''.self::METHOD_DEL_NUMBERS_AND_USERS.'');

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
            ->method(self::METHOD_ADD_NUMBERS);

        $this->keeperMock
            ->expects($this->never())
            ->method(self::METHOD_DEL_NUMBERS_AND_USERS);

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
            ->method(self::METHOD_ADD_NUMBERS);

        $this->keeperMock
            ->expects($this->never())
            ->method(self::METHOD_DEL_NUMBERS_AND_USERS);

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
            ->method(self::METHOD_ADD_NUMBERS);

        $this->keeperMock
            ->expects($this->never())
            ->method(self::METHOD_DEL_NUMBERS_AND_USERS);

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
