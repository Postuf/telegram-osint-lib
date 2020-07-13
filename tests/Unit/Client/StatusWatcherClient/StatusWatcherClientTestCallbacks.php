<?php

declare(strict_types=1);

namespace Unit\Client\StatusWatcherClient;

use TelegramOSINT\Client\StatusWatcherClient\Models\HiddenStatus;
use TelegramOSINT\Client\StatusWatcherClient\Models\User;
use TelegramOSINT\Client\StatusWatcherClient\StatusWatcherCallbacks;

class StatusWatcherClientTestCallbacks implements StatusWatcherCallbacks
{
    /** @var int[] */
    private $onlineRecords = [];
    /** @var int[] */
    private $offlineRecords = [];
    /** @var int[] */
    private $hidRecords = [];
    /** @var int[] */
    private $inaccurate = [];

    /**
     * @param User $user
     * @param int  $expires
     *
     * @return void
     */
    public function onUserOnline(User $user, int $expires): void
    {
        $phone = $user->getPhone();
        if(isset($this->onlineRecords[$phone])) {
            $this->onlineRecords[$phone]++;
        } else {
            $this->onlineRecords[$phone] = 1;
        }
    }

    /**
     * @param User $user
     * @param int  $wasOnline
     * @param bool $inaccurate
     *
     * @return void
     */
    public function onUserOffline(User $user, int $wasOnline, bool $inaccurate = false): void
    {
        $phone = $user->getPhone();
        if(isset($this->offlineRecords[$phone])) {
            $this->offlineRecords[$phone]++;
        } else {
            $this->offlineRecords[$phone] = 1;
        }
        if ($inaccurate) {
            if (isset($this->inaccurate[$phone])) {
                $this->inaccurate[$phone]++;
            } else {
                $this->inaccurate[$phone] = 1;
            }
        }
    }

    /**
     * @param User         $user
     * @param HiddenStatus $hiddenStatus
     *
     * @return void
     */
    public function onUserHidStatus(User $user, HiddenStatus $hiddenStatus): void
    {
        $phone = $user->getPhone();
        if(isset($this->hidRecords[$phone])) {
            $this->hidRecords[$phone]++;
        } else {
            $this->hidRecords[$phone] = 1;
        }
    }

    /**
     * @param string $phone
     *
     * @return int
     */
    public function getOnlineTriggersCntFor(string $phone): int
    {
        return $this->onlineRecords[$phone] ?? 0;
    }

    /**
     * @param string $phone
     *
     * @return int
     */
    public function getOfflineTriggersCntFor(string $phone): int
    {
        return $this->offlineRecords[$phone] ?? 0;
    }

    /**
     * @param string $phone
     *
     * @return int
     */
    public function getPollTriggersCntFor(string $phone): int
    {
        return $this->inaccurate[$phone] ?? 0;
    }

    /**
     * @param string $phone
     *
     * @return int
     */
    public function getHidTriggersCntFor(string $phone): int
    {
        return $this->hidRecords[$phone] ?? 0;
    }

    public function onUserPhoneChange(User $user, string $phone): void
    {
    }

    public function onUserNameChange(User $user, string $username): void
    {
    }
}
