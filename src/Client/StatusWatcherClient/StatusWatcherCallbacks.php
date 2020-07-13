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
    public function onUserOnline(User $user, int $expires): void;

    /**
     * @param User $user
     * @param int  $wasOnline
     * @param bool $inaccurate
     */
    public function onUserOffline(User $user, int $wasOnline, bool $inaccurate = false): void;

    /**
     * @param User         $user
     * @param HiddenStatus $hiddenStatusState
     */
    public function onUserHidStatus(User $user, HiddenStatus $hiddenStatusState): void;

    public function onUserPhoneChange(User $user, string $phone): void;

    public function onUserNameChange(User $user, string $username): void;
}
