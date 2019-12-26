<?php

namespace Client\StatusWatcherClient;

use Client\StatusWatcherClient\Models\HiddenStatus;
use Client\StatusWatcherClient\Models\User;

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
}
