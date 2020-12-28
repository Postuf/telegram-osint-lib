<?php

declare(strict_types=1);

namespace Unit\Client\StatusWatcherClient;

use TelegramOSINT\Client\BasicClient\BasicClient;
use TelegramOSINT\Client\StatusWatcherClient\StatusWatcherCallbacks;
use TelegramOSINT\Client\StatusWatcherClient\StatusWatcherClient;
use TelegramOSINT\Logger\ClientDebugLogger;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Contact\ContactUser;
use TelegramOSINT\Tools\Clock;

class StatusWatcherClientMock extends StatusWatcherClient
{
    /**
     * @var int
     */
    private int $isUserExpirationChecks = 0;
    /** @var BasicClient|null */
    private ?BasicClient $curClient;

    public function __construct(
        StatusWatcherCallbacks $callbacks,
        ?ClientDebugLogger $logger = null,
        array $startContacts = [],
        ?Clock $clock = null,
        ?BasicClient $basicClient = null
    ) {
        parent::__construct($callbacks, $logger, $startContacts, $clock, $basicClient);
        $this->curClient = $basicClient;
        $this->contactsKeeper = new ContactsKeeperMock($this->basicClient);
    }

    /**
     * @var ContactUser[]
     */
    public function loadMockContacts(array $contacts): void
    {
        $this->contactsKeeper->loadContacts($contacts);
    }

    public function pollMessage(): bool
    {
        if ($this->curClient) {
            return parent::pollMessage();
        }
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
    public function getUserExpirationChecks(): int
    {
        return $this->isUserExpirationChecks;
    }

    protected function throwIfNotLoggedIn(string $message): void
    {
    }

    public function onUserPhoneChange(int $userId, ?string $phone): void
    {
    }

    public function onUserNameChange(int $userId, ?string $username): void
    {
    }
}
