<?php

declare(strict_types=1);

namespace TelegramOSINT\Scenario\PresenceMonitoringScenario;

use TelegramOSINT\Client\AuthKey\AuthKeyCreator;
use TelegramOSINT\Client\StatusWatcherClient\Models\HiddenStatus;
use TelegramOSINT\Client\StatusWatcherClient\Models\ImportResult;
use TelegramOSINT\Client\StatusWatcherClient\Models\User;
use TelegramOSINT\Client\StatusWatcherClient\StatusWatcherCallbacks;
use TelegramOSINT\Client\StatusWatcherClient\StatusWatcherClient;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\Scenario\ClientGenerator;
use TelegramOSINT\Scenario\ScenarioInterface;

class PresenceMonitoringScenario implements ScenarioInterface, StatusWatcherCallbacks
{
    /**
     * @var StatusWatcherClient
     */
    private $client;
    /**
     * @var string
     */
    private $authKey;
    /**
     * @var string[]
     */
    private $numbers;
    /**
     * @var PresenceMonitoringCallbacks
     */
    private $callbacks;
    /** @var ClientGenerator */
    private $generator;

    /**
     * @param array                       $numbers
     * @param PresenceMonitoringCallbacks $callbacks
     * @param ClientGenerator             $clientGenerator
     *
     * @throws TGException
     */
    public function __construct(
        array $numbers,
        PresenceMonitoringCallbacks $callbacks,
        ClientGenerator $clientGenerator
    ) {
        $this->client = $clientGenerator->getStatusWatcherClient($this);
        $this->authKey = $clientGenerator->getAuthKey();
        $this->numbers = $numbers;
        $this->callbacks = $callbacks;
        $this->generator = $clientGenerator;
    }

    /**
     * @param bool $pollAndTerminate
     *
     * @throws TGException
     */
    public function startActions(bool $pollAndTerminate = true): void
    {
        $this->client->login(AuthKeyCreator::createFromString($this->authKey), $this->generator->getProxy());
        $this->client->reloadContacts($this->numbers, [], static function (ImportResult $result) {});
    }

    /**
     * @throws TGException
     */
    public function poll(): void
    {
        $this->client->pollMessage();
    }

    /**
     * @param User $user
     * @param int  $expires
     */
    public function onUserOnline(User $user, int $expires): void
    {
        $phone = $user->getPhone();
        if($phone) {
            $this->callbacks->onOnline($phone);
        }
    }

    /**
     * @param User $user
     * @param int  $wasOnline
     */
    public function onUserOffline(User $user, int $wasOnline): void
    {
        $phone = $user->getPhone();
        if($phone) {
            $this->callbacks->onOffline($phone, $wasOnline);
        }
    }

    /**
     * @param User         $user
     * @param HiddenStatus $hiddenStatusState
     */
    public function onUserHidStatus(User $user, HiddenStatus $hiddenStatusState): void
    {
        $phone = $user->getPhone();
        if($phone) {
            $this->callbacks->onHidden($phone);
        }
    }

    public function onUserPhoneChange(User $user, string $phone): void
    {
    }

    public function onUserNameChange(User $user, string $username): void
    {
    }

    public function onReloadContacts(array $users): void
    {
    }
}
