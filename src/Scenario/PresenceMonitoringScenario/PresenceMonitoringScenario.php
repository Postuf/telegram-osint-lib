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
use TelegramOSINT\Scenario\ClientGeneratorInterface;
use TelegramOSINT\Scenario\ScenarioInterface;
use TelegramOSINT\Scenario\TimeoutScenarioInterface;
use TelegramOSINT\Tools\Clock;
use TelegramOSINT\Tools\DefaultClock;

class PresenceMonitoringScenario implements ScenarioInterface, StatusWatcherCallbacks, TimeoutScenarioInterface
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
    /** @var ClientGeneratorInterface */
    private $generator;
    /** @var float */
    private $timeOut = 0.0;
    /** @var Clock */
    private $clock;

    /**
     * @param array                       $numbers
     * @param PresenceMonitoringCallbacks $callbacks
     * @param ClientGeneratorInterface    $clientGenerator
     * @param Clock|null                  $clock
     *
     * @throws TGException
     */
    public function __construct(
        array $numbers,
        PresenceMonitoringCallbacks $callbacks,
        ClientGeneratorInterface $clientGenerator,
        ?Clock $clock = null
    ) {
        $this->client = $clientGenerator->getStatusWatcherClient($this);
        $this->authKey = $clientGenerator->getAuthKey();
        $this->numbers = $numbers;
        $this->callbacks = $callbacks;
        $this->generator = $clientGenerator;
        $this->clock = $clock ?? new DefaultClock();
    }

    /**
     * @param bool $pollAndTerminate
     *
     * @throws TGException
     */
    public function startActions(bool $pollAndTerminate = true): void
    {
        $this->client->login(AuthKeyCreator::createFromString($this->authKey), $this->generator->getProxy(), function () {
            $this->client->reloadContacts($this->numbers, [], static function (ImportResult $result) {});
        });
        if ($pollAndTerminate) {
            $startTime = $this->clock->microtime(true);
            $lastMonitorTime = $this->clock->time();
            while (true) {
                $this->client->pollMessage();
                if ($this->clock->time() > $lastMonitorTime) {
                    $lastMonitorTime = $this->clock->time();
                    $this->callbacks->tick();
                }
                $this->clock->usleep(50000);
                if ($this->timeOut !== 0.0 && $this->clock->microtime() - $startTime > $this->timeOut) {
                    break;
                }
            }
        }
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
     * @param bool $inaccurate
     */
    public function onUserOffline(User $user, int $wasOnline, bool $inaccurate = false): void
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

    public function setTimeout(float $timeout): void
    {
        $this->timeOut = $timeout;
    }
}
