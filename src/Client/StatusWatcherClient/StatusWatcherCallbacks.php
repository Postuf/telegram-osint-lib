<?php

namespace TelegramOSINT\Client\StatusWatcherClient;

use TelegramOSINT\Client\StatusWatcherClient\Models\HiddenStatus;
use TelegramOSINT\Client\StatusWatcherClient\Models\User;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Contact\ContactUser;

interface StatusWatcherCallbacks
{
    /**
     * @param User $user
     * @param int  $expires
     */
    public function onUserOnline(User $user, int $expires): void;

    /**
     * @param User $user
     * @param int  $wasOnline
     */
    public function onUserOffline(User $user, int $wasOnline): void;

    /**
     * @param User         $user
     * @param HiddenStatus $hiddenStatusState
     */
    public function onUserHidStatus(User $user, HiddenStatus $hiddenStatusState): void;

    public function onUserPhoneChange(User $user, string $phone): void;

    public function onUserNameChange(User $user, string $username): void;

    /**
     * @param ContactUser[] $users
     */
    public function onReloadContacts(array $users): void;
}
