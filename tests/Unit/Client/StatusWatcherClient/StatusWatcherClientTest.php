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

    private $clock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clock = new ControllableClock();
    }

    /**
     * Test that second user add call is postponed
     *
     * @throws TGException|JsonException
     */
    public function test_add_user_postponed(): void
    {
        $callbacks = new StatusWatcherClientTestCallbacks();
        $file = TraceConverterJsonToText::fromFile(__DIR__.self::TRACE_PATH);
        /** @noinspection PhpUnhandledExceptionInspection */
        $trace = json_decode($file, true, 512, JSON_THROW_ON_ERROR);
        /** @noinspection PhpUnhandledExceptionInspection */
        $watcherClient = new StatusWatcherClientMock(
            $callbacks,
            null,
            [],
            $this->clock,
            new NullBasicClientImpl($trace)
        );
        $watcherClient->login(AuthKeyCreator::createFromString(self::DEFAULT_AUTHKEY));
        /* @noinspection PhpUnhandledExceptionInspection */
        $watcherClient->loadMockContacts([
            new ContactUser(AnonymousMessageMock::getContact(self::USER_ID1, self::PHONE1)),
            new ContactUser(AnonymousMessageMock::getContact(self::USER_ID2, self::PHONE2)),
        ]);

        $calledUser1 = false;
        $watcherClient->addUser('test1', static function () use (&$calledUser1) {
            $calledUser1 = true;
        });
        $this->pollMessages($watcherClient);
        $this->assertTrue($calledUser1);

        // second user addition
        $watcherClient->addUser('test2', function () {
            $this->assertFalse(true, 'must not be called');
        });
        $this->pollMessages($watcherClient);
    }

    /**
     * @throws TGException
     */
    public function test_online_trigger_works(): void
    {
        $callbacks = new StatusWatcherClientTestCallbacks();
        /** @noinspection PhpUnhandledExceptionInspection */
        $watcherClient = new StatusWatcherClientMock($callbacks);
        /* @noinspection PhpUnhandledExceptionInspection */
        $watcherClient->loadMockContacts([
            new ContactUser(AnonymousMessageMock::getContact(self::USER_ID1, self::PHONE1)),
            new ContactUser(AnonymousMessageMock::getContact(self::USER_ID2, self::PHONE2)),
        ]);

        /* @noinspection PhpUnhandledExceptionInspection */
        $watcherClient->onMessage(AnonymousMessageMock::getUserOnline(self::USER_ID1));

        $this->assertEquals(1, $callbacks->getOnlineTriggersCntFor(self::PHONE1));
        $this->assertEquals(0, $callbacks->getOfflineTriggersCntFor(self::PHONE1));
        $this->assertEquals(0, $callbacks->getHidTriggersCntFor(self::PHONE1));

        $this->assertEquals(0, $callbacks->getOnlineTriggersCntFor(self::PHONE2));
        $this->assertEquals(0, $callbacks->getOfflineTriggersCntFor(self::PHONE2));
        $this->assertEquals(0, $callbacks->getHidTriggersCntFor(self::PHONE2));
    }

    public function test_offline_trigger_works(): void
    {
        $callbacks = new StatusWatcherClientTestCallbacks();
        /** @noinspection PhpUnhandledExceptionInspection */
        $watcherClient = new StatusWatcherClientMock($callbacks);
        /* @noinspection PhpUnhandledExceptionInspection */
        $watcherClient->loadMockContacts([
            new ContactUser(AnonymousMessageMock::getContact(self::USER_ID1, self::PHONE1)),
            new ContactUser(AnonymousMessageMock::getContact(self::USER_ID2, self::PHONE2)),
        ]);

        /* @noinspection PhpUnhandledExceptionInspection */
        $watcherClient->onMessage(AnonymousMessageMock::getUserOffline(self::USER_ID1));

        $this->assertEquals(0, $callbacks->getOnlineTriggersCntFor(self::PHONE1));
        $this->assertEquals(1, $callbacks->getOfflineTriggersCntFor(self::PHONE1));
        $this->assertEquals(0, $callbacks->getHidTriggersCntFor(self::PHONE1));

        $this->assertEquals(0, $callbacks->getOnlineTriggersCntFor(self::PHONE2));
        $this->assertEquals(0, $callbacks->getOfflineTriggersCntFor(self::PHONE2));
        $this->assertEquals(0, $callbacks->getHidTriggersCntFor(self::PHONE2));
    }

    /**
     * @throws TGException
     */
    public function test_hid_trigger_works(): void
    {
        $callbacks = new StatusWatcherClientTestCallbacks();
        $watcherClient = new StatusWatcherClientMock($callbacks);
        $watcherClient->loadMockContacts([
            new ContactUser(AnonymousMessageMock::getContact(self::USER_ID1, self::PHONE1)),
            new ContactUser(AnonymousMessageMock::getContact(self::USER_ID2, self::PHONE2)),
        ]);

        $watcherClient->onMessage(AnonymousMessageMock::getUserEmpty(self::USER_ID1));
        $watcherClient->onMessage(AnonymousMessageMock::getUserRecently(self::USER_ID1));

        $this->assertEquals(0, $callbacks->getOnlineTriggersCntFor(self::PHONE1));
        $this->assertEquals(0, $callbacks->getOfflineTriggersCntFor(self::PHONE1));
        $this->assertEquals(2, $callbacks->getHidTriggersCntFor(self::PHONE1));

        $this->assertEquals(0, $callbacks->getOnlineTriggersCntFor(self::PHONE2));
        $this->assertEquals(0, $callbacks->getOfflineTriggersCntFor(self::PHONE2));
        $this->assertEquals(0, $callbacks->getHidTriggersCntFor(self::PHONE2));
    }

    /**
     * @throws TGException
     */
    public function test_online_trigger_when_multiple_works_once(): void
    {
        $callbacks = new StatusWatcherClientTestCallbacks();
        $watcherClient = new StatusWatcherClientMock($callbacks);
        $watcherClient->loadMockContacts([
            new ContactUser(AnonymousMessageMock::getContact(self::USER_ID1, self::PHONE1)),
            new ContactUser(AnonymousMessageMock::getContact(self::USER_ID2, self::PHONE2)),
        ]);

        $watcherClient->onMessage(AnonymousMessageMock::getUserOnline(self::USER_ID1));
        $watcherClient->onMessage(AnonymousMessageMock::getUserOnline(self::USER_ID1));
        $watcherClient->onMessage(AnonymousMessageMock::getUserOnline(self::USER_ID1));

        $this->assertEquals(1, $callbacks->getOnlineTriggersCntFor(self::PHONE1));
        $this->assertEquals(0, $callbacks->getOfflineTriggersCntFor(self::PHONE1));
        $this->assertEquals(0, $callbacks->getHidTriggersCntFor(self::PHONE1));

        $this->assertEquals(0, $callbacks->getOnlineTriggersCntFor(self::PHONE2));
        $this->assertEquals(0, $callbacks->getOfflineTriggersCntFor(self::PHONE2));
        $this->assertEquals(0, $callbacks->getHidTriggersCntFor(self::PHONE2));
    }

    /**
     * @throws TGException
     */
    public function test_offline_trigger_when_multiple_works_once(): void
    {
        $callbacks = new StatusWatcherClientTestCallbacks();
        $watcherClient = new StatusWatcherClientMock($callbacks);
        $watcherClient->loadMockContacts([
            new ContactUser(AnonymousMessageMock::getContact(self::USER_ID1, self::PHONE1)),
            new ContactUser(AnonymousMessageMock::getContact(self::USER_ID2, self::PHONE2)),
        ]);

        $watcherClient->onMessage(AnonymousMessageMock::getUserOffline(self::USER_ID1));
        $watcherClient->onMessage(AnonymousMessageMock::getUserOffline(self::USER_ID1));
        $watcherClient->onMessage(AnonymousMessageMock::getUserOffline(self::USER_ID1));

        $this->assertEquals(0, $callbacks->getOnlineTriggersCntFor(self::PHONE1));
        $this->assertEquals(1, $callbacks->getOfflineTriggersCntFor(self::PHONE1));
        $this->assertEquals(0, $callbacks->getHidTriggersCntFor(self::PHONE1));

        $this->assertEquals(0, $callbacks->getOnlineTriggersCntFor(self::PHONE2));
        $this->assertEquals(0, $callbacks->getOfflineTriggersCntFor(self::PHONE2));
        $this->assertEquals(0, $callbacks->getHidTriggersCntFor(self::PHONE2));
    }

    /**
     * @throws TGException
     */
    public function test_online_offline_statuses_rotate_triggers_works_not_once(): void
    {
        $callbacks = new StatusWatcherClientTestCallbacks();
        $watcherClient = new StatusWatcherClientMock($callbacks);
        $watcherClient->loadMockContacts([
            new ContactUser(AnonymousMessageMock::getContact(self::USER_ID1, self::PHONE1)),
            new ContactUser(AnonymousMessageMock::getContact(self::USER_ID2, self::PHONE2)),
        ]);

        $watcherClient->onMessage(AnonymousMessageMock::getUserOffline(self::USER_ID1));
        $watcherClient->onMessage(AnonymousMessageMock::getUserOnline(self::USER_ID1));
        $watcherClient->onMessage(AnonymousMessageMock::getUserOffline(self::USER_ID1));
        $watcherClient->onMessage(AnonymousMessageMock::getUserOnline(self::USER_ID1));
        $watcherClient->onMessage(AnonymousMessageMock::getUserOffline(self::USER_ID1));

        $this->assertEquals(2, $callbacks->getOnlineTriggersCntFor(self::PHONE1));
        $this->assertEquals(3, $callbacks->getOfflineTriggersCntFor(self::PHONE1));
        $this->assertEquals(0, $callbacks->getHidTriggersCntFor(self::PHONE1));

        $this->assertEquals(0, $callbacks->getOnlineTriggersCntFor(self::PHONE2));
        $this->assertEquals(0, $callbacks->getOfflineTriggersCntFor(self::PHONE2));
        $this->assertEquals(0, $callbacks->getHidTriggersCntFor(self::PHONE2));
    }

    /**
     * @throws TGException
     */
    public function test_recently_status_clears_online_trigger_works_twice(): void
    {
        $callbacks = new StatusWatcherClientTestCallbacks();
        $watcherClient = new StatusWatcherClientMock($callbacks);
        $watcherClient->loadMockContacts([
            new ContactUser(AnonymousMessageMock::getContact(self::USER_ID1, self::PHONE1)),
            new ContactUser(AnonymousMessageMock::getContact(self::USER_ID2, self::PHONE2)),
        ]);

        $watcherClient->onMessage(AnonymousMessageMock::getUserOnline(self::USER_ID1));
        $watcherClient->onMessage(AnonymousMessageMock::getUserRecently(self::USER_ID1));
        $watcherClient->onMessage(AnonymousMessageMock::getUserOnline(self::USER_ID1));

        $this->assertEquals(2, $callbacks->getOnlineTriggersCntFor(self::PHONE1));
        $this->assertEquals(0, $callbacks->getOfflineTriggersCntFor(self::PHONE1));
        $this->assertEquals(1, $callbacks->getHidTriggersCntFor(self::PHONE1));

        $this->assertEquals(0, $callbacks->getOnlineTriggersCntFor(self::PHONE2));
        $this->assertEquals(0, $callbacks->getOfflineTriggersCntFor(self::PHONE2));
        $this->assertEquals(0, $callbacks->getHidTriggersCntFor(self::PHONE2));
    }

    /**
     * @throws TGException
     */
    public function test_empty_status_clears_online_trigger_works_twice(): void
    {
        $callbacks = new StatusWatcherClientTestCallbacks();
        $watcherClient = new StatusWatcherClientMock($callbacks);
        $watcherClient->loadMockContacts([
            new ContactUser(AnonymousMessageMock::getContact(self::USER_ID1, self::PHONE1)),
            new ContactUser(AnonymousMessageMock::getContact(self::USER_ID2, self::PHONE2)),
        ]);

        $watcherClient->onMessage(AnonymousMessageMock::getUserOnline(self::USER_ID1));
        $watcherClient->onMessage(AnonymousMessageMock::getUserEmpty(self::USER_ID1));
        $watcherClient->onMessage(AnonymousMessageMock::getUserOnline(self::USER_ID1));

        $this->assertEquals(2, $callbacks->getOnlineTriggersCntFor(self::PHONE1));
        $this->assertEquals(0, $callbacks->getOfflineTriggersCntFor(self::PHONE1));
        $this->assertEquals(1, $callbacks->getHidTriggersCntFor(self::PHONE1));

        $this->assertEquals(0, $callbacks->getOnlineTriggersCntFor(self::PHONE2));
        $this->assertEquals(0, $callbacks->getOfflineTriggersCntFor(self::PHONE2));
        $this->assertEquals(0, $callbacks->getHidTriggersCntFor(self::PHONE2));
    }

    /**
     * @throws TGException
     */
    public function test_empty_status_importation_online_trigger_works(): void
    {
        $callbacks = new StatusWatcherClientTestCallbacks();
        $watcherClient = new StatusWatcherClientMock($callbacks);
        $watcherClient->loadMockContacts([
            new ContactUser(AnonymousMessageMock::getContact(self::USER_ID1, self::PHONE1)),
            new ContactUser(AnonymousMessageMock::getContact(self::USER_ID2, self::PHONE2)),
        ]);

        $watcherClient->onMessage(AnonymousMessageMock::getImportedContact(self::USER_ID1, self::PHONE1, 'online'));
        $watcherClient->onMessage(AnonymousMessageMock::getUserOffline(self::USER_ID1));
        $watcherClient->onMessage(AnonymousMessageMock::getUserOnline(self::USER_ID1));
        $watcherClient->onMessage(AnonymousMessageMock::getUserRecently(self::USER_ID1));

        $this->assertEquals(2, $callbacks->getOnlineTriggersCntFor(self::PHONE1));
        $this->assertEquals(1, $callbacks->getOfflineTriggersCntFor(self::PHONE1));
        $this->assertEquals(1, $callbacks->getHidTriggersCntFor(self::PHONE1));

        $this->assertEquals(0, $callbacks->getOnlineTriggersCntFor(self::PHONE2));
        $this->assertEquals(0, $callbacks->getOfflineTriggersCntFor(self::PHONE2));
        $this->assertEquals(0, $callbacks->getHidTriggersCntFor(self::PHONE2));
    }

    /**
     * @throws TGException
     */
    public function test_empty_status_importation_online_trigger_works_once(): void
    {
        $callbacks = new StatusWatcherClientTestCallbacks();
        $watcherClient = new StatusWatcherClientMock($callbacks);
        $watcherClient->loadMockContacts([
            new ContactUser(AnonymousMessageMock::getContact(self::USER_ID1, self::PHONE1)),
            new ContactUser(AnonymousMessageMock::getContact(self::USER_ID2, self::PHONE2)),
        ]);

        $watcherClient->onMessage(AnonymousMessageMock::getImportedContact(self::USER_ID1, self::PHONE1, 'online'));
        $watcherClient->onMessage(AnonymousMessageMock::getUserOnline(self::USER_ID1));

        $this->assertEquals(1, $callbacks->getOnlineTriggersCntFor(self::PHONE1));
        $this->assertEquals(0, $callbacks->getOfflineTriggersCntFor(self::PHONE1));
        $this->assertEquals(0, $callbacks->getHidTriggersCntFor(self::PHONE1));

        $this->assertEquals(0, $callbacks->getOnlineTriggersCntFor(self::PHONE2));
        $this->assertEquals(0, $callbacks->getOfflineTriggersCntFor(self::PHONE2));
        $this->assertEquals(0, $callbacks->getHidTriggersCntFor(self::PHONE2));
    }

    /**
     * @throws TGException
     */
    public function test_empty_status_after_importation_recently_trigger_works(): void
    {
        $callbacks = new StatusWatcherClientTestCallbacks();
        $watcherClient = new StatusWatcherClientMock($callbacks);
        $watcherClient->loadMockContacts([
            new ContactUser(AnonymousMessageMock::getContact(self::USER_ID1, self::PHONE1)),
            new ContactUser(AnonymousMessageMock::getContact(self::USER_ID2, self::PHONE2)),
        ]);

        $watcherClient->onMessage(AnonymousMessageMock::getImportedContact(self::USER_ID1, self::PHONE1, 'recently'));
        $watcherClient->onMessage(AnonymousMessageMock::getUserOnline(self::USER_ID1));

        $this->assertEquals(1, $callbacks->getOnlineTriggersCntFor(self::PHONE1));
        $this->assertEquals(0, $callbacks->getOfflineTriggersCntFor(self::PHONE1));
        $this->assertEquals(1, $callbacks->getHidTriggersCntFor(self::PHONE1));

        $this->assertEquals(0, $callbacks->getOnlineTriggersCntFor(self::PHONE2));
        $this->assertEquals(0, $callbacks->getOfflineTriggersCntFor(self::PHONE2));
        $this->assertEquals(0, $callbacks->getHidTriggersCntFor(self::PHONE2));
    }

    /**
     * @throws TGException
     */
    public function test_empty_status_after_importation_offline_trigger_works(): void
    {
        $callbacks = new StatusWatcherClientTestCallbacks();
        $watcherClient = new StatusWatcherClientMock($callbacks);
        $watcherClient->loadMockContacts([
            new ContactUser(AnonymousMessageMock::getContact(self::USER_ID1, self::PHONE1)),
            new ContactUser(AnonymousMessageMock::getContact(self::USER_ID2, self::PHONE2)),
        ]);

        $watcherClient->onMessage(AnonymousMessageMock::getImportedContact(self::USER_ID1, self::PHONE1, 'offline'));
        $watcherClient->onMessage(AnonymousMessageMock::getUserOnline(self::USER_ID1));
        $watcherClient->onMessage(AnonymousMessageMock::getUserOffline(self::USER_ID1));

        $this->assertEquals(1, $callbacks->getOnlineTriggersCntFor(self::PHONE1));
        $this->assertEquals(2, $callbacks->getOfflineTriggersCntFor(self::PHONE1));
        $this->assertEquals(0, $callbacks->getHidTriggersCntFor(self::PHONE1));

        $this->assertEquals(0, $callbacks->getOnlineTriggersCntFor(self::PHONE2));
        $this->assertEquals(0, $callbacks->getOfflineTriggersCntFor(self::PHONE2));
        $this->assertEquals(0, $callbacks->getHidTriggersCntFor(self::PHONE2));
    }

    /**
     * @throws TGException
     */
    public function test_online_expiration_must_expire(): void
    {
        $callbacks = new StatusWatcherClientTestCallbacks();
        $watcherClient = new StatusWatcherClientMock($callbacks);
        $watcherClient->loadMockContacts([
            new ContactUser(AnonymousMessageMock::getContact(self::USER_ID1, self::PHONE1)),
            new ContactUser(AnonymousMessageMock::getContact(self::USER_ID2, self::PHONE2)),
        ]);

        $watcherClient->onMessage(AnonymousMessageMock::getUserOnline(self::USER_ID1, time() + 1));
        $start = time();
        while(time() - $start < 3) {
            $watcherClient->pollMessage();
        }

        $this->assertEquals(1, $callbacks->getOnlineTriggersCntFor(self::PHONE1));
        $this->assertEquals(1, $callbacks->getOfflineTriggersCntFor(self::PHONE1));
        $this->assertEquals(0, $callbacks->getHidTriggersCntFor(self::PHONE1));

        $this->assertEquals(0, $callbacks->getOnlineTriggersCntFor(self::PHONE2));
        $this->assertEquals(0, $callbacks->getOfflineTriggersCntFor(self::PHONE2));
        $this->assertEquals(0, $callbacks->getHidTriggersCntFor(self::PHONE2));
    }

    /**
     * @throws TGException
     */
    public function test_online_expiration_must_not_expire(): void
    {
        $callbacks = new StatusWatcherClientTestCallbacks();
        $watcherClient = new StatusWatcherClientMock($callbacks);
        $watcherClient->loadMockContacts([
            new ContactUser(AnonymousMessageMock::getContact(self::USER_ID1, self::PHONE1)),
            new ContactUser(AnonymousMessageMock::getContact(self::USER_ID2, self::PHONE2)),
        ]);

        $watcherClient->onMessage(AnonymousMessageMock::getUserOnline(self::USER_ID1, time() + 3));
        $start = time();
        while(time() - $start < 2) {
            $watcherClient->pollMessage();
        }

        $this->assertEquals(1, $callbacks->getOnlineTriggersCntFor(self::PHONE1));
        $this->assertEquals(0, $callbacks->getOfflineTriggersCntFor(self::PHONE1));
        $this->assertEquals(0, $callbacks->getHidTriggersCntFor(self::PHONE1));

        $this->assertEquals(0, $callbacks->getOnlineTriggersCntFor(self::PHONE2));
        $this->assertEquals(0, $callbacks->getOfflineTriggersCntFor(self::PHONE2));
        $this->assertEquals(0, $callbacks->getHidTriggersCntFor(self::PHONE2));
    }

    /**
     * @throws TGException
     */
    public function test_online_expiration_update_must_not_expire(): void
    {
        $callbacks = new StatusWatcherClientTestCallbacks();
        $watcherClient = new StatusWatcherClientMock($callbacks);
        $watcherClient->loadMockContacts([
            new ContactUser(AnonymousMessageMock::getContact(self::USER_ID1, self::PHONE1)),
            new ContactUser(AnonymousMessageMock::getContact(self::USER_ID2, self::PHONE2)),
        ]);

        $watcherClient->onMessage(AnonymousMessageMock::getUserOnline(self::USER_ID2, time()));
        $watcherClient->onMessage(AnonymousMessageMock::getUserOnline(self::USER_ID2, time() + 1));
        $watcherClient->onMessage(AnonymousMessageMock::getUserOnline(self::USER_ID2, time() + 3));
        $start = time();
        while(time() - $start < 2) {
            $watcherClient->pollMessage();
        }

        $this->assertEquals(0, $callbacks->getOnlineTriggersCntFor(self::PHONE1));
        $this->assertEquals(0, $callbacks->getOfflineTriggersCntFor(self::PHONE1));
        $this->assertEquals(0, $callbacks->getHidTriggersCntFor(self::PHONE1));

        $this->assertEquals(1, $callbacks->getOnlineTriggersCntFor(self::PHONE2));
        $this->assertEquals(0, $callbacks->getOfflineTriggersCntFor(self::PHONE2));
        $this->assertEquals(0, $callbacks->getHidTriggersCntFor(self::PHONE2));
    }

    /**
     * @throws TGException
     */
    public function test_expired_statuses_checked(): void
    {
        $callbacks = new StatusWatcherClientTestCallbacks();
        $watcherClient = new StatusWatcherClientMock($callbacks);

        $watcherClient->loadMockContacts([
            new ContactUser(AnonymousMessageMock::getContact(self::USER_ID1, self::PHONE1)),
            new ContactUser(AnonymousMessageMock::getContact(self::USER_ID2, self::PHONE2)),
        ]);

        $watcherClient->pollMessage();
        $watcherClient->pollMessage();
        $watcherClient->pollMessage();

        $this->assertEquals(3, $watcherClient->getUserExpirationChecks());

    }

    /**
     * @throws TGException
     */
    public function test_add_contacts_bad_number(): void
    {
        $callbacks = new StatusWatcherClientTestCallbacks();
        $watcherClient = new StatusWatcherClientMock($callbacks);

        try{
            $watcherClient->addNumbers(['ufheorhwewq'], static function (ImportResult $result) {});
            $this->assertFalse(true, 'bad format not detected');
        } catch (TGException $e){
            $this->assertEquals(TGException::ERR_CLIENT_BAD_NUMBER_FORMAT, $e->getCode());
        }

        try{
            $watcherClient->addNumbers(['7+9169904863'], static function (ImportResult $result) {});
            $this->assertFalse(true, 'bad format not detected');
        } catch (TGException $e){
            $this->assertEquals(TGException::ERR_CLIENT_BAD_NUMBER_FORMAT, $e->getCode());
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
}
