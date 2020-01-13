<?php

declare(strict_types=1);

namespace Tests\Tests\Client;

use Client\StatusWatcherClient\Models\ImportResult;
use Exception\TGException;
use PHPUnit\Framework\TestCase;
use Tests\Tests\Client\StatusWatcherClient\AnonymousMessageMock;
use Tests\Tests\Client\StatusWatcherClient\StatusWatcherClientMock;
use Tests\Tests\Client\StatusWatcherClient\StatusWatcherClientTestCallbacks;
use TLMessage\TLMessage\ServerMessages\Contact\ContactUser;

class StatusWatcherClientTest extends TestCase
{
    public function test_online_trigger_works(): void
    {
        $callbacks = new StatusWatcherClientTestCallbacks();
        /** @noinspection PhpUnhandledExceptionInspection */
        $watcherClient = new StatusWatcherClientMock($callbacks);
        /* @noinspection PhpUnhandledExceptionInspection */
        $watcherClient->loadMockContacts([
            new ContactUser(AnonymousMessageMock::getContact(0x1000000, '7999888777666')),
            new ContactUser(AnonymousMessageMock::getContact(0x1000002, '7999888777667')),
        ]);

        /* @noinspection PhpUnhandledExceptionInspection */
        $watcherClient->onMessage(AnonymousMessageMock::getUserOnline(0x1000000));

        $this->assertTrue($callbacks->getOnlineTriggersCntFor('7999888777666') == 1);
        $this->assertTrue($callbacks->getOfflineTriggersCntFor('7999888777666') == 0);
        $this->assertTrue($callbacks->getHidTriggersCntFor('7999888777666') == 0);

        $this->assertTrue($callbacks->getOnlineTriggersCntFor('7999888777667') == 0);
        $this->assertTrue($callbacks->getOfflineTriggersCntFor('7999888777667') == 0);
        $this->assertTrue($callbacks->getHidTriggersCntFor('7999888777667') == 0);
    }

    public function test_offline_trigger_works(): void
    {
        $callbacks = new StatusWatcherClientTestCallbacks();
        /** @noinspection PhpUnhandledExceptionInspection */
        $watcherClient = new StatusWatcherClientMock($callbacks);
        /* @noinspection PhpUnhandledExceptionInspection */
        $watcherClient->loadMockContacts([
            new ContactUser(AnonymousMessageMock::getContact(0x1000000, '7999888777666')),
            new ContactUser(AnonymousMessageMock::getContact(0x1000002, '7999888777667')),
        ]);

        /* @noinspection PhpUnhandledExceptionInspection */
        $watcherClient->onMessage(AnonymousMessageMock::getUserOffline(0x1000000));

        $this->assertTrue($callbacks->getOnlineTriggersCntFor('7999888777666') == 0);
        $this->assertTrue($callbacks->getOfflineTriggersCntFor('7999888777666') == 1);
        $this->assertTrue($callbacks->getHidTriggersCntFor('7999888777666') == 0);

        $this->assertTrue($callbacks->getOnlineTriggersCntFor('7999888777667') == 0);
        $this->assertTrue($callbacks->getOfflineTriggersCntFor('7999888777667') == 0);
        $this->assertTrue($callbacks->getHidTriggersCntFor('7999888777667') == 0);
    }

    /**
     * @throws TGException
     */
    public function test_hid_trigger_works(): void
    {
        $callbacks = new StatusWatcherClientTestCallbacks();
        $watcherClient = new StatusWatcherClientMock($callbacks);
        $watcherClient->loadMockContacts([
            new ContactUser(AnonymousMessageMock::getContact(0x1000000, '7999888777666')),
            new ContactUser(AnonymousMessageMock::getContact(0x1000002, '7999888777667')),
        ]);

        $watcherClient->onMessage(AnonymousMessageMock::getUserEmpty(0x1000000));
        $watcherClient->onMessage(AnonymousMessageMock::getUserRecently(0x1000000));

        $this->assertTrue($callbacks->getOnlineTriggersCntFor('7999888777666') == 0);
        $this->assertTrue($callbacks->getOfflineTriggersCntFor('7999888777666') == 0);
        $this->assertTrue($callbacks->getHidTriggersCntFor('7999888777666') == 2);

        $this->assertTrue($callbacks->getOnlineTriggersCntFor('7999888777667') == 0);
        $this->assertTrue($callbacks->getOfflineTriggersCntFor('7999888777667') == 0);
        $this->assertTrue($callbacks->getHidTriggersCntFor('7999888777667') == 0);
    }

    /**
     * @throws TGException
     */
    public function test_online_trigger_when_multiple_works_once(): void
    {
        $callbacks = new StatusWatcherClientTestCallbacks();
        $watcherClient = new StatusWatcherClientMock($callbacks);
        $watcherClient->loadMockContacts([
            new ContactUser(AnonymousMessageMock::getContact(0x1000000, '7999888777666')),
            new ContactUser(AnonymousMessageMock::getContact(0x1000002, '7999888777667')),
        ]);

        $watcherClient->onMessage(AnonymousMessageMock::getUserOnline(0x1000000));
        $watcherClient->onMessage(AnonymousMessageMock::getUserOnline(0x1000000));
        $watcherClient->onMessage(AnonymousMessageMock::getUserOnline(0x1000000));

        $this->assertTrue($callbacks->getOnlineTriggersCntFor('7999888777666') == 1);
        $this->assertTrue($callbacks->getOfflineTriggersCntFor('7999888777666') == 0);
        $this->assertTrue($callbacks->getHidTriggersCntFor('7999888777666') == 0);

        $this->assertTrue($callbacks->getOnlineTriggersCntFor('7999888777667') == 0);
        $this->assertTrue($callbacks->getOfflineTriggersCntFor('7999888777667') == 0);
        $this->assertTrue($callbacks->getHidTriggersCntFor('7999888777667') == 0);
    }

    /**
     * @throws TGException
     */
    public function test_offline_trigger_when_multiple_works_once(): void
    {
        $callbacks = new StatusWatcherClientTestCallbacks();
        $watcherClient = new StatusWatcherClientMock($callbacks);
        $watcherClient->loadMockContacts([
            new ContactUser(AnonymousMessageMock::getContact(0x1000000, '7999888777666')),
            new ContactUser(AnonymousMessageMock::getContact(0x1000002, '7999888777667')),
        ]);

        $watcherClient->onMessage(AnonymousMessageMock::getUserOffline(0x1000000));
        $watcherClient->onMessage(AnonymousMessageMock::getUserOffline(0x1000000));
        $watcherClient->onMessage(AnonymousMessageMock::getUserOffline(0x1000000));

        $this->assertTrue($callbacks->getOnlineTriggersCntFor('7999888777666') == 0);
        $this->assertTrue($callbacks->getOfflineTriggersCntFor('7999888777666') == 1);
        $this->assertTrue($callbacks->getHidTriggersCntFor('7999888777666') == 0);

        $this->assertTrue($callbacks->getOnlineTriggersCntFor('7999888777667') == 0);
        $this->assertTrue($callbacks->getOfflineTriggersCntFor('7999888777667') == 0);
        $this->assertTrue($callbacks->getHidTriggersCntFor('7999888777667') == 0);
    }

    /**
     * @throws TGException
     */
    public function test_online_offline_statuses_rotate_triggers_works_not_once(): void
    {
        $callbacks = new StatusWatcherClientTestCallbacks();
        $watcherClient = new StatusWatcherClientMock($callbacks);
        $watcherClient->loadMockContacts([
            new ContactUser(AnonymousMessageMock::getContact(0x1000000, '7999888777666')),
            new ContactUser(AnonymousMessageMock::getContact(0x1000002, '7999888777667')),
        ]);

        $watcherClient->onMessage(AnonymousMessageMock::getUserOffline(0x1000000));
        $watcherClient->onMessage(AnonymousMessageMock::getUserOnline(0x1000000));
        $watcherClient->onMessage(AnonymousMessageMock::getUserOffline(0x1000000));
        $watcherClient->onMessage(AnonymousMessageMock::getUserOnline(0x1000000));
        $watcherClient->onMessage(AnonymousMessageMock::getUserOffline(0x1000000));

        $this->assertTrue($callbacks->getOnlineTriggersCntFor('7999888777666') == 2);
        $this->assertTrue($callbacks->getOfflineTriggersCntFor('7999888777666') == 3);
        $this->assertTrue($callbacks->getHidTriggersCntFor('7999888777666') == 0);

        $this->assertTrue($callbacks->getOnlineTriggersCntFor('7999888777667') == 0);
        $this->assertTrue($callbacks->getOfflineTriggersCntFor('7999888777667') == 0);
        $this->assertTrue($callbacks->getHidTriggersCntFor('7999888777667') == 0);
    }

    /**
     * @throws TGException
     */
    public function test_recently_status_clears_online_trigger_works_twice(): void
    {
        $callbacks = new StatusWatcherClientTestCallbacks();
        $watcherClient = new StatusWatcherClientMock($callbacks);
        $watcherClient->loadMockContacts([
            new ContactUser(AnonymousMessageMock::getContact(0x1000000, '7999888777666')),
            new ContactUser(AnonymousMessageMock::getContact(0x1000002, '7999888777667')),
        ]);

        $watcherClient->onMessage(AnonymousMessageMock::getUserOnline(0x1000000));
        $watcherClient->onMessage(AnonymousMessageMock::getUserRecently(0x1000000));
        $watcherClient->onMessage(AnonymousMessageMock::getUserOnline(0x1000000));

        $this->assertTrue($callbacks->getOnlineTriggersCntFor('7999888777666') == 2);
        $this->assertTrue($callbacks->getOfflineTriggersCntFor('7999888777666') == 0);
        $this->assertTrue($callbacks->getHidTriggersCntFor('7999888777666') == 1);

        $this->assertTrue($callbacks->getOnlineTriggersCntFor('7999888777667') == 0);
        $this->assertTrue($callbacks->getOfflineTriggersCntFor('7999888777667') == 0);
        $this->assertTrue($callbacks->getHidTriggersCntFor('7999888777667') == 0);
    }

    /**
     * @throws TGException
     */
    public function test_empty_status_clears_online_trigger_works_twice(): void
    {
        $callbacks = new StatusWatcherClientTestCallbacks();
        $watcherClient = new StatusWatcherClientMock($callbacks);
        $watcherClient->loadMockContacts([
            new ContactUser(AnonymousMessageMock::getContact(0x1000000, '7999888777666')),
            new ContactUser(AnonymousMessageMock::getContact(0x1000002, '7999888777667')),
        ]);

        $watcherClient->onMessage(AnonymousMessageMock::getUserOnline(0x1000000));
        $watcherClient->onMessage(AnonymousMessageMock::getUserEmpty(0x1000000));
        $watcherClient->onMessage(AnonymousMessageMock::getUserOnline(0x1000000));

        $this->assertTrue($callbacks->getOnlineTriggersCntFor('7999888777666') == 2);
        $this->assertTrue($callbacks->getOfflineTriggersCntFor('7999888777666') == 0);
        $this->assertTrue($callbacks->getHidTriggersCntFor('7999888777666') == 1);

        $this->assertTrue($callbacks->getOnlineTriggersCntFor('7999888777667') == 0);
        $this->assertTrue($callbacks->getOfflineTriggersCntFor('7999888777667') == 0);
        $this->assertTrue($callbacks->getHidTriggersCntFor('7999888777667') == 0);
    }

    /**
     * @throws TGException
     */
    public function test_empty_status_importation_online_trigger_works(): void
    {
        $callbacks = new StatusWatcherClientTestCallbacks();
        $watcherClient = new StatusWatcherClientMock($callbacks);
        $watcherClient->loadMockContacts([
            new ContactUser(AnonymousMessageMock::getContact(0x1000000, '7999888777666')),
            new ContactUser(AnonymousMessageMock::getContact(0x1000002, '7999888777667')),
        ]);

        $watcherClient->onMessage(AnonymousMessageMock::getImportedContact(0x1000000, '7999888777666', 'online'));
        $watcherClient->onMessage(AnonymousMessageMock::getUserOffline(0x1000000));
        $watcherClient->onMessage(AnonymousMessageMock::getUserOnline(0x1000000));
        $watcherClient->onMessage(AnonymousMessageMock::getUserRecently(0x1000000));

        $this->assertTrue($callbacks->getOnlineTriggersCntFor('7999888777666') == 2);
        $this->assertTrue($callbacks->getOfflineTriggersCntFor('7999888777666') == 1);
        $this->assertTrue($callbacks->getHidTriggersCntFor('7999888777666') == 1);

        $this->assertTrue($callbacks->getOnlineTriggersCntFor('7999888777667') == 0);
        $this->assertTrue($callbacks->getOfflineTriggersCntFor('7999888777667') == 0);
        $this->assertTrue($callbacks->getHidTriggersCntFor('7999888777667') == 0);
    }

    /**
     * @throws TGException
     */
    public function test_empty_status_importation_online_trigger_works_once(): void
    {
        $callbacks = new StatusWatcherClientTestCallbacks();
        $watcherClient = new StatusWatcherClientMock($callbacks);
        $watcherClient->loadMockContacts([
            new ContactUser(AnonymousMessageMock::getContact(0x1000000, '7999888777666')),
            new ContactUser(AnonymousMessageMock::getContact(0x1000002, '7999888777667')),
        ]);

        $watcherClient->onMessage(AnonymousMessageMock::getImportedContact(0x1000000, '7999888777666', 'online'));
        $watcherClient->onMessage(AnonymousMessageMock::getUserOnline(0x1000000));

        $this->assertTrue($callbacks->getOnlineTriggersCntFor('7999888777666') == 1);
        $this->assertTrue($callbacks->getOfflineTriggersCntFor('7999888777666') == 0);
        $this->assertTrue($callbacks->getHidTriggersCntFor('7999888777666') == 0);

        $this->assertTrue($callbacks->getOnlineTriggersCntFor('7999888777667') == 0);
        $this->assertTrue($callbacks->getOfflineTriggersCntFor('7999888777667') == 0);
        $this->assertTrue($callbacks->getHidTriggersCntFor('7999888777667') == 0);
    }

    /**
     * @throws TGException
     */
    public function test_empty_status_after_importation_recently_trigger_works(): void
    {
        $callbacks = new StatusWatcherClientTestCallbacks();
        $watcherClient = new StatusWatcherClientMock($callbacks);
        $watcherClient->loadMockContacts([
            new ContactUser(AnonymousMessageMock::getContact(0x1000000, '7999888777666')),
            new ContactUser(AnonymousMessageMock::getContact(0x1000002, '7999888777667')),
        ]);

        $watcherClient->onMessage(AnonymousMessageMock::getImportedContact(0x1000000, '7999888777666', 'recently'));
        $watcherClient->onMessage(AnonymousMessageMock::getUserOnline(0x1000000));

        $this->assertTrue($callbacks->getOnlineTriggersCntFor('7999888777666') == 1);
        $this->assertTrue($callbacks->getOfflineTriggersCntFor('7999888777666') == 0);
        $this->assertTrue($callbacks->getHidTriggersCntFor('7999888777666') == 1);

        $this->assertTrue($callbacks->getOnlineTriggersCntFor('7999888777667') == 0);
        $this->assertTrue($callbacks->getOfflineTriggersCntFor('7999888777667') == 0);
        $this->assertTrue($callbacks->getHidTriggersCntFor('7999888777667') == 0);
    }

    /**
     * @throws TGException
     */
    public function test_empty_status_after_importation_offline_trigger_works(): void
    {
        $callbacks = new StatusWatcherClientTestCallbacks();
        $watcherClient = new StatusWatcherClientMock($callbacks);
        $watcherClient->loadMockContacts([
            new ContactUser(AnonymousMessageMock::getContact(0x1000000, '7999888777666')),
            new ContactUser(AnonymousMessageMock::getContact(0x1000002, '7999888777667')),
        ]);

        $watcherClient->onMessage(AnonymousMessageMock::getImportedContact(0x1000000, '7999888777666', 'offline'));
        $watcherClient->onMessage(AnonymousMessageMock::getUserOnline(0x1000000));
        $watcherClient->onMessage(AnonymousMessageMock::getUserOffline(0x1000000));

        $this->assertTrue($callbacks->getOnlineTriggersCntFor('7999888777666') == 1);
        $this->assertTrue($callbacks->getOfflineTriggersCntFor('7999888777666') == 2);
        $this->assertTrue($callbacks->getHidTriggersCntFor('7999888777666') == 0);

        $this->assertTrue($callbacks->getOnlineTriggersCntFor('7999888777667') == 0);
        $this->assertTrue($callbacks->getOfflineTriggersCntFor('7999888777667') == 0);
        $this->assertTrue($callbacks->getHidTriggersCntFor('7999888777667') == 0);
    }

    /**
     * @throws TGException
     */
    public function test_online_expiration_must_expire(): void
    {
        $callbacks = new StatusWatcherClientTestCallbacks();
        $watcherClient = new StatusWatcherClientMock($callbacks);
        $watcherClient->loadMockContacts([
            new ContactUser(AnonymousMessageMock::getContact(0x1000000, '7999888777666')),
            new ContactUser(AnonymousMessageMock::getContact(0x1000002, '7999888777667')),
        ]);

        $watcherClient->onMessage(AnonymousMessageMock::getUserOnline(0x1000000, time() + 1));
        $start = time();
        while(time() - $start < 3)
            $watcherClient->pollMessage();

        $this->assertTrue($callbacks->getOnlineTriggersCntFor('7999888777666') == 1);
        $this->assertTrue($callbacks->getOfflineTriggersCntFor('7999888777666') == 1);
        $this->assertTrue($callbacks->getHidTriggersCntFor('7999888777666') == 0);

        $this->assertTrue($callbacks->getOnlineTriggersCntFor('7999888777667') == 0);
        $this->assertTrue($callbacks->getOfflineTriggersCntFor('7999888777667') == 0);
        $this->assertTrue($callbacks->getHidTriggersCntFor('7999888777667') == 0);
    }

    /**
     * @throws TGException
     */
    public function test_online_expiration_must_not_expire(): void
    {
        $callbacks = new StatusWatcherClientTestCallbacks();
        $watcherClient = new StatusWatcherClientMock($callbacks);
        $watcherClient->loadMockContacts([
            new ContactUser(AnonymousMessageMock::getContact(0x1000000, '7999888777666')),
            new ContactUser(AnonymousMessageMock::getContact(0x1000002, '7999888777667')),
        ]);

        $watcherClient->onMessage(AnonymousMessageMock::getUserOnline(0x1000000, time() + 3));
        $start = time();
        while(time() - $start < 2)
            $watcherClient->pollMessage();

        $this->assertEquals(1, $callbacks->getOnlineTriggersCntFor('7999888777666'));
        $this->assertEquals(0, $callbacks->getOfflineTriggersCntFor('7999888777666'));
        $this->assertEquals(0, $callbacks->getHidTriggersCntFor('7999888777666'));

        $this->assertEquals(0, $callbacks->getOnlineTriggersCntFor('7999888777667'));
        $this->assertEquals(0, $callbacks->getOfflineTriggersCntFor('7999888777667'));
        $this->assertEquals(0, $callbacks->getHidTriggersCntFor('7999888777667'));
    }

    /**
     * @throws TGException
     */
    public function test_online_expiration_update_must_not_expire(): void
    {
        $callbacks = new StatusWatcherClientTestCallbacks();
        $watcherClient = new StatusWatcherClientMock($callbacks);
        $watcherClient->loadMockContacts([
            new ContactUser(AnonymousMessageMock::getContact(0x1000000, '7999888777666')),
            new ContactUser(AnonymousMessageMock::getContact(0x1000002, '7999888777667')),
        ]);

        $watcherClient->onMessage(AnonymousMessageMock::getUserOnline(0x1000002, time()));
        $watcherClient->onMessage(AnonymousMessageMock::getUserOnline(0x1000002, time() + 1));
        $watcherClient->onMessage(AnonymousMessageMock::getUserOnline(0x1000002, time() + 3));
        $start = time();
        while(time() - $start < 2)
            $watcherClient->pollMessage();

        $this->assertEquals(0, $callbacks->getOnlineTriggersCntFor('7999888777666'));
        $this->assertEquals(0, $callbacks->getOfflineTriggersCntFor('7999888777666'));
        $this->assertEquals(0, $callbacks->getHidTriggersCntFor('7999888777666'));

        $this->assertEquals(1, $callbacks->getOnlineTriggersCntFor('7999888777667'));
        $this->assertEquals(0, $callbacks->getOfflineTriggersCntFor('7999888777667'));
        $this->assertEquals(0, $callbacks->getHidTriggersCntFor('7999888777667'));
    }

    /**
     * @throws TGException
     */
    public function test_expired_statuses_checked(): void
    {
        $callbacks = new StatusWatcherClientTestCallbacks();
        $watcherClient = new StatusWatcherClientMock($callbacks);

        $watcherClient->loadMockContacts([
            new ContactUser(AnonymousMessageMock::getContact(0x1000000, '7999888777666')),
            new ContactUser(AnonymousMessageMock::getContact(0x1000002, '7999888777667')),
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
            $watcherClient->addNumbers(['ufheorhwewq'], function (ImportResult $result) {});
            $this->assertFalse(true, 'bad format not detected');
        } catch (TGException $e){
            $this->assertEquals($e->getCode(), TGException::ERR_CLIENT_BAD_NUMBER_FORMAT);
        }

        try{
            $watcherClient->addNumbers(['7+9169904863'], function (ImportResult $result) {});
            $this->assertFalse(true, 'bad format not detected');
        } catch (TGException $e){
            $this->assertEquals($e->getCode(), TGException::ERR_CLIENT_BAD_NUMBER_FORMAT);
        }
    }
}
