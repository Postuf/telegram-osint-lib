<?php

declare(strict_types=1);

namespace Unit\Client\StatusWatcherClient;

use TelegramOSINT\Client\BasicClient\BasicClient;
use TelegramOSINT\Client\StatusWatcherClient\ContactsKeeper;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Contact\ContactUser;

class ContactsKeeperMock extends ContactsKeeper
{
    /**
     * @var ContactUser[]
     */
    private $contacts;

    /* @noinspection PhpMissingParentConstructorInspection */
    public function __construct(?BasicClient $client)
    {
        //parent::__construct($client);
    }

    /**
     * @param ContactUser[] $contacts
     */
    public function loadContacts(array $contacts)
    {
        $this->contacts = $contacts;
    }

    public function getUserById(int $userId, callable $onSuccess)
    {
        foreach ($this->contacts as $contact)
            if($contact->getUserId() == $userId)
                $onSuccess($contact);
    }

    protected function contactsLoaded(callable $onLoadedCallback): bool
    {
        return true;
    }
}
