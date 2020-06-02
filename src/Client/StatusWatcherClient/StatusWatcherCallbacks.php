<?php

namespace TelegramOSINT\Client\StatusWatcherClient;

use TelegramOSINT\Client\StatusWatcherClient\Models\HiddenStatus;
use TelegramOSINT\Client\StatusWatcherClient\Models\User;

interface StatusWatcherCallbacks
{
    /**
     * @param User $user
     * @param int  $expires
     */
    public function onUserOnline(User $user, int $expires);

    /**
     * @param User $user
     * @param int  $wasOnline
     */
    public function onUserOffline(User $user, int $wasOnline);

    /**
     * @param User         $user
     * @param HiddenStatus $hiddenStatusState
     */
    public function onUserHidStatus(User $user, HiddenStatus $hiddenStatusState);

    public function onUserPhoneChange(User $user, string $phone);

    public function onUserNameChange(User $user, string $username);
}
