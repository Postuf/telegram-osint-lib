<?php


namespace Client\StatusWatcherClient;


use Client\StatusWatcherClient\Models\HiddenStatus;
use Client\StatusWatcherClient\Models\User;

interface StatusWatcherCallbacks
{

    /**
     * @param User $user
     * @param int $expires
     * @return
     */
    public function onUserOnline(User $user, int $expires);

    /**
     * @param User $user
     * @param int $wasOnline
     * @return
     */
    public function onUserOffline(User $user, int $wasOnline);

    /**
     * @param User $user
     * @param HiddenStatus $hiddenStatusState
     * @return
     */
    public function onUserHidStatus(User $user, HiddenStatus $hiddenStatusState);


}