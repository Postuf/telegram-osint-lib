<?php

declare(strict_types=1);

namespace Unit\Client\StatusWatcherClient;

use TelegramOSINT\Client\StatusWatcherClient\Models\HiddenStatus;
use TelegramOSINT\Client\StatusWatcherClient\Models\User;
use TelegramOSINT\Client\StatusWatcherClient\StatusWatcherCallbacks;

class StatusWatcherClientTestCallbacks implements StatusWatcherCallbacks
{
    private $onlineRecords = [];
    private $offlineRecords = [];
    private $hidRecords = [];

    /**
     * @param User $user
     * @param int  $expires
     *
     * @return void
     */
    public function onUserOnline(User $user, int $expires)
    {
        $phone = $user->getPhone();
        if(isset($this->onlineRecords[$phone]))
            $this->onlineRecords[$phone]++;
        else
            $this->onlineRecords[$phone] = 1;
    }

    /**
     * @param User $user
     * @param int  $wasOnline
     *
     * @return void
     */
    public function onUserOffline(User $user, int $wasOnline)
    {
        $phone = $user->getPhone();
        if(isset($this->offlineRecords[$phone]))
            $this->offlineRecords[$phone]++;
        else
            $this->offlineRecords[$phone] = 1;
    }

    /**
     * @param User         $user
     * @param HiddenStatus $hiddenStatus
     *
     * @return void
     */
    public function onUserHidStatus(User $user, HiddenStatus $hiddenStatus)
    {
        $phone = $user->getPhone();
        if(isset($this->hidRecords[$phone]))
            $this->hidRecords[$phone]++;
        else
            $this->hidRecords[$phone] = 1;
    }

    /**
     * @param string $phone
     *
     * @return int
     */
    public function getOnlineTriggersCntFor(string $phone)
    {
        return isset($this->onlineRecords[$phone]) ? $this->onlineRecords[$phone] : 0;
    }

    /**
     * @param string $phone
     *
     * @return int
     */
    public function getOfflineTriggersCntFor(string $phone)
    {
        return isset($this->offlineRecords[$phone]) ? $this->offlineRecords[$phone] : 0;
    }

    /**
     * @param string $phone
     *
     * @return int
     */
    public function getHidTriggersCntFor(string $phone)
    {
        return isset($this->hidRecords[$phone]) ? $this->hidRecords[$phone] : 0;
    }

    public function onUserPhoneChange(User $user, string $phone)
    {
    }

    public function onUserNameChange(User $user, string $username)
    {
    }
}
