<?php

declare(strict_types=1);

namespace Unit\Client\StatusWatcherClient;

use TelegramOSINT\Client\StatusWatcherClient\StatusWatcherCallbacks;
use TelegramOSINT\Client\StatusWatcherClient\StatusWatcherClient;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Contact\ContactUser;

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
    public function loadMockContacts(array $contacts): void
    {
        $this->mockContacts = $contacts;
        $this->contactKeeper->loadContacts($contacts);
    }

    public function pollMessage(): bool
    {
        $this->checkOnlineStatusesExpired();

        return true;
    }

    protected function checkOnlineStatusesExpired(): void
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

    protected function throwIfNotLoggedIn(string $message)
    {

    }
}
