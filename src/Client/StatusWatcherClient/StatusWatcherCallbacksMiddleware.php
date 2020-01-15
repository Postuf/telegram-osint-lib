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
    public function onUserOnline(int $userId, int $expires);

    /**
     * @param int $userId
     * @param int $wasOnline
     */
    public function onUserOffline(int $userId, int $wasOnline);

    /**
     * @param int          $userId
     * @param HiddenStatus $hiddenStatusState
     */
    public function onUserHidStatus(int $userId, HiddenStatus $hiddenStatusState);

    /**
     * @param ImportedContacts $contactsObject
     */
    public function onContactsImported(ImportedContacts $contactsObject);
}
