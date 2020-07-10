<?php

declare(strict_types=1);

namespace Unit\Client\StatusWatcherClient;

use TelegramOSINT\Client\BasicClient\BasicClient;
use TelegramOSINT\Client\DeferredClient;
use TelegramOSINT\Client\StatusWatcherClient\ContactsKeeper;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Contact\ContactUser;
use TelegramOSINT\Tools\Clock;

class ContactsKeeperMock extends ContactsKeeper
{
    /**
     * @var ContactUser[]
     */
    private $contacts;

    /**
     * @param BasicClient|null    $client
     * @param DeferredClient|null $deferredClient
     * @param Clock|null          $clock
     */
    public function __construct(?BasicClient $client, ?DeferredClient $deferredClient = null, ?Clock $clock = null)
    {
        if ($client && $deferredClient) {
            parent::__construct($client, $deferredClient, $clock);
        }
    }

    /**
     * @param ContactUser[] $contacts
     */
    public function loadContacts(array $contacts): void
    {
        $this->contacts = $contacts;
    }

    public function getUserById(int $userId, callable $onSuccess): void
    {
        foreach ($this->contacts as $contact) {
            if ($contact->getUserId() === $userId) {
                $onSuccess($contact);
            }
        }
    }

    protected function contactsLoaded(callable $onLoadedCallback): bool
    {
        return true;
    }
}
