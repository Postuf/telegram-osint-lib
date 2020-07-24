<?php

declare(strict_types=1);

namespace Unit\Client\StatusWatcherClient;

use Helpers\Mocks\ControllableClock;
use Helpers\NullBasicClientImpl;
use Helpers\TraceConverter\TraceConverterJsonToText;
use JsonException;
use PHPUnit\Framework\TestCase;
use TelegramOSINT\Client\AuthKey\AuthKeyCreator;
use TelegramOSINT\Client\StatusWatcherClient\Models\ImportResult;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Contact\ContactUser;

class StatusWatcherClientTest extends TestCase
{
    private const DEFAULT_AUTHKEY = '77476991876:7b22646576696365223a226875617765695354462d414c3130222c22616e64726f696453646b56657273696f6e223a2253444b203237222c2266697273744e616d65223a224b6972616e222c226c6173744e616d65223a224b656e6e79222c226465766963654c616e67223a22656e2d7573222c226170704c616e67223a22656e222c2261707056657273696f6e223a22342e392e31222c2261707056657273696f6e436f6465223a223133363137222c226c6179657256657273696f6e223a38357d:Tz/zv6i70SsFHsKvvkKs6VYeb8OUDC0zQSn8lEkfBeD2Un3hey/BcM5UeT+5NbIiW3Ioy0BqoluLGViG6comBiCdKiYDHeNAgv8CuiqsVwI1uQXIEM6kIKA5SJOmc+mDIEy2hxuAfFVpuNL3cBKicwQ4YcofdEh/na7W/IUt5AcwBpI//Gco6JjjD4zhwGretLslmMooeADlaO0f2+1J+7qjXTTen3FT6ozjYaGyIIJeGtX8Qnjqva60pBkTAor1t1E5eghpJVTuOzZK/5eAoVyl9JG7g5kFfPQGQ70mIuQFkgpZ7MhD0Jqvm4H/GcAoQd9iNqXFVMYWl298GM7qBQ==:7b2263726561746564223a313533393638363236332c226170695f6964223a362c2264635f6964223a322c2264635f6970223a223134392e3135342e3136372e3530222c2264635f706f7274223a3434337d';
    private const PHONE1 = '7999888777666';
    private const PHONE2 = '7999888777667';
    private const USER_ID1 = 0x1000000;
    private const USER_ID2 = 0x1000002;
    protected const TRACE_PATH = '/../traces/user-contacts.json';
    private const STATUS_OFFLINE = 'offline';
    private const STATUS_RECENTLY = 'recently';
    private const STATUS_ONLINE = 'online';

    /** @var StatusWatcherClientTestCallbacks */
    private StatusWatcherClientTestCallbacks $callbacks;
    /** @var ControllableClock */
    private ControllableClock $clock;
    /** @var StatusWatcherClientMock */
    private StatusWatcherClientMock $watcherClient;

    /**
     * @throws TGException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->clock = new ControllableClock();
        $this->callbacks = new StatusWatcherClientTestCallbacks();
        $this->watcherClient = new StatusWatcherClientMock(
            $this->callbacks,
            null,
            [],
            $this->clock
        );
        $this->watcherClient->loadMockContacts([
            new ContactUser(AnonymousMessageMock::getContact(self::USER_ID1, self::PHONE1)),
            new ContactUser(AnonymousMessageMock::getContact(self::USER_ID2, self::PHONE2)),
        ]);
    }

    /**
     * Test that second user add call is not called if time not passed
     *
     * @throws TGException|JsonException
     */
    public function test_add_user_postponed(): void
    {
        $calledUser1 = false;
        $watcherClient = $this->prepareClientWithTrace(static function (StatusWatcherClientMock $watcherClient) use (&$calledUser1) {
            $watcherClient->addUser('test1', static function () use (&$calledUser1) {
                $calledUser1 = true;
            });
        });
        $this->pollMessages($watcherClient);
        self::assertTrue($calledUser1);

        // second user addition
        $watcherClient->addUser('test2', function () {
            $this->assertFalse(true, 'must not be called');
        });
        $this->pollMessages($watcherClient);
    }

    /**
     * Test that second user add call is postponed
     *
     * @throws TGException|JsonException
     */
    public function test_add_user_called_in_time(): void
    {
        $calledUser1 = false;
        $watcherClient = $this->prepareClientWithTrace(static function (StatusWatcherClientMock $watcherClient) use (&$calledUser1) {
            $watcherClient->addUser('test1', static function () use (&$calledUser1) {
                $calledUser1 = true;
            });
        });
        $this->pollMessages($watcherClient);
        self::assertTrue($calledUser1);

        // second user addition
        $calledUser2 = false;
        $watcherClient->addUser('test2', static function () use (&$calledUser2) {
            $calledUser2 = true;
        });

        $this->pollMessages($watcherClient);
        self::assertFalse($calledUser2);
        $this->clock->usleep(3 * ControllableClock::SECONDS_MS);
        $this->pollMessages($watcherClient);
        self::assertTrue($calledUser2);
    }

    /**
     * @throws TGException
     */
    public function test_online_trigger_works(): void
    {
        $this->watcherClient->onMessage(AnonymousMessageMock::getUserOnline(self::USER_ID1));

        $this->assertStatusTriggerCount(self::PHONE1, [1, 0, 0]);
        $this->assertStatusTriggerCount(self::PHONE2, [0, 0, 0]);
    }

    public function test_offline_trigger_works(): void
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        $this->watcherClient->onMessage(AnonymousMessageMock::getUserOffline(self::USER_ID1));

        $this->assertStatusTriggerCount(self::PHONE1, [0, 1, 0]);
        $this->assertStatusTriggerCount(self::PHONE2, [0, 0, 0]);
    }

    /**
     * @throws TGException
     */
    public function test_hid_trigger_works(): void
    {
        $this->watcherClient->onMessage(AnonymousMessageMock::getUserEmpty(self::USER_ID1));
        $this->watcherClient->onMessage(AnonymousMessageMock::getUserRecently(self::USER_ID1));

        $this->assertStatusTriggerCount(self::PHONE1, [0, 0, 2]);
        $this->assertStatusTriggerCount(self::PHONE2, [0, 0, 0]);
    }

    /**
     * @throws TGException
     */
    public function test_online_trigger_when_multiple_works_once(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->watcherClient->onMessage(AnonymousMessageMock::getUserOnline(self::USER_ID1));
        }

        $this->assertStatusTriggerCount(self::PHONE1, [1, 0, 0]);
        $this->assertStatusTriggerCount(self::PHONE2, [0, 0, 0]);
    }

    /**
     * @throws TGException
     */
    public function test_offline_trigger_when_multiple_works_once(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->watcherClient->onMessage(AnonymousMessageMock::getUserOffline(self::USER_ID1));
        }

        $this->assertStatusTriggerCount(self::PHONE1, [0, 1, 0]);
        $this->assertStatusTriggerCount(self::PHONE2, [0, 0, 0]);
    }

    /**
     * @throws TGException
     */
    public function test_online_offline_statuses_rotate_triggers_works_not_once(): void
    {
        $this->watcherClient->onMessage(AnonymousMessageMock::getUserOffline(self::USER_ID1));
        $this->watcherClient->onMessage(AnonymousMessageMock::getUserOnline(self::USER_ID1));
        $this->watcherClient->onMessage(AnonymousMessageMock::getUserOffline(self::USER_ID1));
        $this->watcherClient->onMessage(AnonymousMessageMock::getUserOnline(self::USER_ID1));
        $this->watcherClient->onMessage(AnonymousMessageMock::getUserOffline(self::USER_ID1));

        $this->assertStatusTriggerCount(self::PHONE1, [2, 3, 0]);
        $this->assertStatusTriggerCount(self::PHONE2, [0, 0, 0]);
    }

    /**
     * @throws TGException
     */
    public function test_recently_status_clears_online_trigger_works_twice(): void
    {
        $this->watcherClient->onMessage(AnonymousMessageMock::getUserOnline(self::USER_ID1));
        $this->watcherClient->onMessage(AnonymousMessageMock::getUserRecently(self::USER_ID1));
        $this->watcherClient->onMessage(AnonymousMessageMock::getUserOnline(self::USER_ID1));

        $this->assertStatusTriggerCount(self::PHONE1, [2, 0, 1]);
        $this->assertStatusTriggerCount(self::PHONE2, [0, 0, 0]);
    }

    /**
     * @throws TGException
     */
    public function test_empty_status_clears_online_trigger_works_twice(): void
    {
        $this->watcherClient->onMessage(AnonymousMessageMock::getUserOnline(self::USER_ID1));
        $this->watcherClient->onMessage(AnonymousMessageMock::getUserEmpty(self::USER_ID1));
        $this->watcherClient->onMessage(AnonymousMessageMock::getUserOnline(self::USER_ID1));

        $this->assertStatusTriggerCount(self::PHONE1, [2, 0, 1]);
        $this->assertStatusTriggerCount(self::PHONE2, [0, 0, 0]);
    }

    /**
     * @throws TGException
     */
    public function test_empty_status_importation_online_trigger_works(): void
    {
        $this->watcherClient->onMessage(AnonymousMessageMock::getImportedContact(self::USER_ID1, self::PHONE1, self::STATUS_ONLINE));
        $this->watcherClient->onMessage(AnonymousMessageMock::getUserOffline(self::USER_ID1));
        $this->watcherClient->onMessage(AnonymousMessageMock::getUserOnline(self::USER_ID1));
        $this->watcherClient->onMessage(AnonymousMessageMock::getUserRecently(self::USER_ID1));

        $this->assertStatusTriggerCount(self::PHONE1, [2, 1, 1]);
        $this->assertStatusTriggerCount(self::PHONE2, [0, 0, 0]);
    }

    /**
     * @throws TGException
     */
    public function test_empty_status_importation_online_trigger_works_once(): void
    {
        $this->watcherClient->onMessage(AnonymousMessageMock::getImportedContact(self::USER_ID1, self::PHONE1, self::STATUS_ONLINE));
        $this->watcherClient->onMessage(AnonymousMessageMock::getUserOnline(self::USER_ID1));

        $this->assertStatusTriggerCount(self::PHONE1, [1, 0, 0]);
        $this->assertStatusTriggerCount(self::PHONE2, [0, 0, 0]);
    }

    /**
     * @throws TGException
     */
    public function test_empty_status_after_importation_recently_trigger_works(): void
    {
        $this->watcherClient->onMessage(AnonymousMessageMock::getImportedContact(self::USER_ID1, self::PHONE1, self::STATUS_RECENTLY));
        $this->watcherClient->onMessage(AnonymousMessageMock::getUserOnline(self::USER_ID1));

        $this->assertStatusTriggerCount(self::PHONE1, [1, 0, 1]);
        $this->assertStatusTriggerCount(self::PHONE2, [0, 0, 0]);
    }

    /**
     * @throws TGException
     */
    public function test_empty_status_after_importation_offline_trigger_works(): void
    {
        $this->watcherClient->onMessage(AnonymousMessageMock::getImportedContact(self::USER_ID1, self::PHONE1, self::STATUS_OFFLINE));
        $this->watcherClient->onMessage(AnonymousMessageMock::getUserOnline(self::USER_ID1));
        $this->watcherClient->onMessage(AnonymousMessageMock::getUserOffline(self::USER_ID1));
        $this->assertStatusTriggerCount(self::PHONE1, [1, 2, 0]);
        $this->assertStatusTriggerCount(self::PHONE2, [0, 0, 0]);
    }

    /**
     * Проверка, что для importedContacts выставляется неточный статус
     *
     * @throws TGException
     */
    public function test_importation_offline_flag_works(): void
    {
        $this->watcherClient->onMessage(AnonymousMessageMock::getImportedContact(self::USER_ID1, self::PHONE1, self::STATUS_OFFLINE));

        $this->assertStatusTriggerCount(self::PHONE1, [0, 1, 0, 1]);
    }

    /**
     * @throws TGException
     */
    public function test_online_expiration_must_expire(): void
    {
        $this->watcherClient->onMessage(AnonymousMessageMock::getUserOnline(self::USER_ID1, $this->clock->time() + 1));
        $this->pollMessagesUntil(3);

        $this->assertStatusTriggerCount(self::PHONE1, [1, 1, 0]);
        $this->assertStatusTriggerCount(self::PHONE2, [0, 0, 0]);
    }

    /**
     * @throws TGException
     */
    public function test_online_expiration_must_not_expire(): void
    {
        $this->watcherClient->onMessage(AnonymousMessageMock::getUserOnline(self::USER_ID1, $this->clock->time() + 3));
        $this->pollMessagesUntil(2);

        $this->assertStatusTriggerCount(self::PHONE1, [1, 0, 0]);

        $this->assertStatusTriggerCount(self::PHONE2, [0, 0, 0]);
    }

    /**
     * @throws TGException
     */
    public function test_online_expiration_update_must_not_expire(): void
    {
        $this->watcherClient->onMessage(AnonymousMessageMock::getUserOnline(self::USER_ID2, $this->clock->time()));
        $this->watcherClient->onMessage(AnonymousMessageMock::getUserOnline(self::USER_ID2, $this->clock->time() + 1));
        $this->watcherClient->onMessage(AnonymousMessageMock::getUserOnline(self::USER_ID2, $this->clock->time() + 3));
        $this->pollMessagesUntil(2);

        $this->assertStatusTriggerCount(self::PHONE1, [0, 0, 0]);
        $this->assertStatusTriggerCount(self::PHONE2, [1, 0, 0]);
    }

    /**
     * @throws TGException
     */
    public function test_expired_statuses_checked(): void
    {
        $this->watcherClient->pollMessage();
        $this->watcherClient->pollMessage();
        $this->watcherClient->pollMessage();

        self::assertEquals(3, $this->watcherClient->getUserExpirationChecks());
    }

    public function test_add_contacts_bad_number(): void
    {
        try {
            $this->watcherClient->addNumbers(['ufheorhwewq'], static function (ImportResult $result) {});
            self::assertFalse(true, 'bad format not detected');
        } catch (TGException $e) {
            self::assertEquals(TGException::ERR_CLIENT_BAD_NUMBER_FORMAT, $e->getCode());
        }

        try {
            $this->watcherClient->addNumbers(['7+9169904863'], static function (ImportResult $result) {});
            self::assertFalse(true, 'bad format not detected');
        } catch (TGException $e) {
            self::assertEquals(TGException::ERR_CLIENT_BAD_NUMBER_FORMAT, $e->getCode());
        }
    }

    /**
     * @param StatusWatcherClientMock $watcherClient
     *
     * @throws TGException
     */
    private function pollMessages(StatusWatcherClientMock $watcherClient): void
    {
        for ($i = 0; $i < 10; $i++) {
            $watcherClient->pollMessage();
        }
    }

    /**
     * @param string $phone
     * @param array  $statuses [online, offline, hid, ?inaccurate]
     */
    private function assertStatusTriggerCount(
        string $phone,
        array $statuses
    ): void {
        self::assertEquals($statuses[0], $this->callbacks->getOnlineTriggersCntFor($phone));
        self::assertEquals($statuses[1], $this->callbacks->getOfflineTriggersCntFor($phone));
        self::assertEquals($statuses[2], $this->callbacks->getHidTriggersCntFor($phone));
        if (isset($statuses[3])) {
            self::assertEquals($statuses[3], $this->callbacks->getPollTriggersCntFor($phone));
        }
    }

    /**
     * @param int $time
     *
     * @throws TGException
     */
    private function pollMessagesUntil(int $time): void
    {
        $start = $this->clock->time();
        while ($this->clock->time() - $start < $time) {
            $this->watcherClient->pollMessage();
            $this->clock->usleep(50000);
        }
    }

    /**
     * @param callable $cb function(WatcherClient $client)
     *
     * @throws JsonException
     * @throws TGException
     *
     * @return StatusWatcherClientMock
     */
    private function prepareClientWithTrace(callable $cb): StatusWatcherClientMock
    {
        $file = TraceConverterJsonToText::fromFile(__DIR__.self::TRACE_PATH);
        $trace = json_decode($file, true, 512, JSON_THROW_ON_ERROR);
        $watcherClient = new StatusWatcherClientMock(
            $this->callbacks,
            null,
            [],
            $this->clock,
            new NullBasicClientImpl($trace)
        );
        $watcherClient->login(
            AuthKeyCreator::createFromString(self::DEFAULT_AUTHKEY),
            null,
            static function () use ($watcherClient, $cb) {
                $watcherClient->loadMockContacts([
                    new ContactUser(AnonymousMessageMock::getContact(self::USER_ID1, self::PHONE1)),
                    new ContactUser(AnonymousMessageMock::getContact(self::USER_ID2, self::PHONE2)),
                ]);
                $cb($watcherClient);
            }
        );

        return $watcherClient;
    }
}
