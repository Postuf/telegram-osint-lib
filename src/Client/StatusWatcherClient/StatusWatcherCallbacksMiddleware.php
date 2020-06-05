<?php

namespace TelegramOSINT\Client\StatusWatcherClient;

use TelegramOSINT\Client\StatusWatcherClient\Models\HiddenStatus;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Contact\ImportedContacts;

interface StatusWatcherCallbacksMiddleware
{
    /**
     * @param int $userId
     * @param int $expires
     */
    public function onUserOnline(int $userId, int $expires): void;

    /**
     * @param int $userId
     * @param int $wasOnline
     */
    public function onUserOffline(int $userId, int $wasOnline): void;

    /**
     * @param int          $userId
     * @param HiddenStatus $hiddenStatusState
     */
    public function onUserHidStatus(int $userId, HiddenStatus $hiddenStatusState): void;

    /**
     * @param ImportedContacts $contactsObject
     */
    public function onContactsImported(ImportedContacts $contactsObject): void;

    public function onUserPhoneChange(int $userId, string $phone): void;

    public function onUserNameChange(int $userId, string $username): void;
}
