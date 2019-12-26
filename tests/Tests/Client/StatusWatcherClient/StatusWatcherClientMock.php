<?php

namespace Tests\Tests\Client\StatusWatcherClient;

use Client\StatusWatcherClient\StatusWatcherCallbacks;
use Client\StatusWatcherClient\StatusWatcherClient;
use TLMessage\TLMessage\ServerMessages\Contact\ContactUser;

class StatusWatcherClientMock extends StatusWatcherClient
{
    /**
     * @var ContactUser[]
     */
    private $mockContacts;
    /**
     * @var int
     */
    private $isUserExpirationChecks = 0;

    public function __construct(StatusWatcherCallbacks $callbacks)
    {
        parent::__construct($callbacks);
        $this->contactKeeper = new ContactsKeeperMock(null);
    }

    /**
     * @var ContactUser[]
     */
    public function loadMockContacts(array $contacts)
    {
        $this->mockContacts = $contacts;
        $this->contactKeeper->loadContacts($contacts);
    }

    public function pollMessage()
    {
        $this->checkOnlineStatusesExpired();
    }

    protected function checkOnlineStatusesExpired()
    {
        parent::checkOnlineStatusesExpired();
        $this->isUserExpirationChecks++;
    }

    /**
     * @return int
     */
    public function getUserExpirationChecks()
    {
        return $this->isUserExpirationChecks;
    }

    protected function throwIfNotLoggedIn()
    {

    }
}
