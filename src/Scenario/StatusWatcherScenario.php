<?php

declare(strict_types=1);

namespace TelegramOSINT\Scenario;

use TelegramOSINT\Client\AuthKey\AuthKeyCreator;
use TelegramOSINT\Client\Client;
use TelegramOSINT\Client\StatusWatcherClient\Models\HiddenStatus;
use TelegramOSINT\Client\StatusWatcherClient\Models\ImportResult;
use TelegramOSINT\Client\StatusWatcherClient\Models\User;
use TelegramOSINT\Client\StatusWatcherClient\StatusWatcherCallbacks;
use TelegramOSINT\Client\StatusWatcherClient\StatusWatcherClient;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\LibConfig;
use TelegramOSINT\Logger\ClientDebugLogger;
use TelegramOSINT\Tools\Clock;
use TelegramOSINT\Tools\DefaultClock;
use TelegramOSINT\Tools\Proxy;

/**
 * Status watcher client
 *
 * Subscribes to a bunch of accounts and then
 */
class StatusWatcherScenario implements StatusWatcherCallbacks, ClientDebugLogger, ScenarioInterface
{
    private const DEFAULT_TTL = 1000000;
    private const INITIAL_POLL_CYCLE_COUNT = 10;
    private const SLEEP_MICRO_SECONDS = 50000;

    /**
     * @var StatusWatcherClient
     */
    private $client;
    /**
     * @var string
     */
    private $authKey;
    /**
     * @var Proxy
     */
    private $proxy;
    /** @var string[] */
    private $numbers;
    /** @var string[] */
    private $users;
    /** @var int */
    private $stopAfter;
    /** @var ClientDebugLogger|null */
    private $logger;
    /** @var Clock */
    private $clock;

    /**
     * @param string[]                      $numbers
     * @param array                         $users
     * @param ClientGeneratorInterface|null $generator
     * @param Proxy|null                    $proxy
     * @param int                           $stopAfter
     * @param ClientDebugLogger|null        $logger
     *
     * @throws TGException
     */
    public function __construct(
        array $numbers,
        array $users = [],
        ?ClientGeneratorInterface $generator = null,
        ?Proxy $proxy = null,
        int $stopAfter = self::DEFAULT_TTL,
        ClientDebugLogger $logger = null
    ) {
        if (!$generator) {
            $generator = new ClientGenerator(LibConfig::ENV_AUTHKEY, $proxy);
        }

        $this->authKey = $generator->getAuthKey();
        $this->numbers = $numbers;
        $this->users = $users;

        $this->client = $generator->getStatusWatcherClient($this);
        $this->proxy = $proxy;
        $this->stopAfter = $stopAfter;
        $this->logger = $logger;
        $this->clock = new DefaultClock();
    }

    /**
     * @param bool $pollAndTerminate
     *
     * @throws TGException
     */
    public function startActions(bool $pollAndTerminate = true): void
    {
        if ($pollAndTerminate) {
            $this->monitorPhones();
        }
    }

    private function log(string $message): void
    {
        if ($this->logger) {
            $this->logger->debugLibLog(__CLASS__, $message);
        }
    }

    /**
     * @throws TGException
     */
    private function monitorPhones(): void
    {
        $authKey = AuthKeyCreator::createFromString($this->authKey);
        $this->client->login($authKey, $this->proxy);

        /* add via phone numbers */
        $monitoringPhones = $this->numbers;
        $lastContactsCleaningTime = 0;
        $this->client->reloadContacts($monitoringPhones, $this->users, function (ImportResult $result) use (&$lastContactsCleaningTime) {
            $lastContactsCleaningTime = $this->clock->time();
            $this->log('Contacts imported total:'.count($result->importedPhones).PHP_EOL);
            $this->log('Replaced phones:'.print_r($result->replacedPhones, true).PHP_EOL);
        });

        /* add via user names */
        foreach ($this->users as $user) {
            $this->client->addUser($user, function (bool $addResult) use ($user) {
                $time = time();
                $this->log("$user added: $addResult at $time");
            });
        }

        // wait a little between operations in order to get possible exceptions
        // it is preferable only once after first call of import/add contacts
        for($i = 0; $i < self::INITIAL_POLL_CYCLE_COUNT; $i++) {
            $this->pollClientCycle($this->client);
        }

        $start = $this->clock->time();

        while(true){

            $this->pollClientCycle($this->client);

            if($this->clock->time() - $start > $this->stopAfter && !$this->client->hasDeferredCalls()) {
                $this->client->terminate();
                break;
            }

            if($lastContactsCleaningTime > 0 && $this->clock->time() - $lastContactsCleaningTime > 5) {
                // remove contact by name
                $lastContactsCleaningTime = $this->clock->time();
            }
        }

        $this->client->cleanContactsBook(function () {
            $this->log('Contacts cleaned'.PHP_EOL);
        });
    }

    /**
     * @param Client $client
     *
     * @throws TGException
     */
    private function pollClientCycle(Client $client): void
    {
        try {
            $client->pollMessage();
        } catch (TGException $e) {
            if ($e->getCode() === TGException::ERR_CLIENT_ADD_USERNAME_ALREADY_IN_ADDRESS_BOOK) {
                $this->log('Error: '.$e->getMessage().PHP_EOL);
            } else {
                throw $e;
            }
        }

        // save some CPU in infinite cycles
        usleep(self::SLEEP_MICRO_SECONDS);
    }

    public function onUserOnline(User $user, int $expires): void
    {
        $this->log('======================='.PHP_EOL);
        $this->log('User '.$user->getPhone().'|'.$user->getUsername().' now online. Expires= '.date('d/m/Y H:i:s', $expires).PHP_EOL);
        $this->log('======================='.PHP_EOL);
    }

    public function onUserOffline(User $user, int $wasOnline): void
    {
        $this->log('======================='.PHP_EOL);
        $this->log('User '.$user->getPhone().'|'.$user->getUsername().' now offline. Last seen = '.date('d/m/Y H:i:s', $wasOnline).PHP_EOL);
        $this->log('======================='.PHP_EOL);
    }

    /**
     * @param User         $user
     * @param HiddenStatus $hiddenStatusState
     */
    public function onUserHidStatus(User $user, HiddenStatus $hiddenStatusState): void
    {
        $hiddenStatusStr = 'unknown';
        switch ($hiddenStatusState){
            case HiddenStatus::HIDDEN_SEEN_LAST_MONTH:
                $hiddenStatusStr = 'last month';
                break;
            case HiddenStatus::HIDDEN_SEEN_LAST_WEEK:
                $hiddenStatusStr = 'last week';
                break;
            case HiddenStatus::HIDDEN_SEEN_RECENTLY:
                $hiddenStatusStr = 'today or yesterday';
                break;
            case HiddenStatus::HIDDEN_EMPTY:
                $hiddenStatusStr = 'unknown';
                break;
            case HiddenStatus::HIDDEN_SEEN_LONG_AGO:
                $hiddenStatusStr = 'long ago';
                break;
        }

        $this->log('======================='.PHP_EOL);
        $this->log('User '.$user->getPhone().'/'.$user->getUsername().' hid his status'.PHP_EOL);
        $this->log('Hidden status info: '.$hiddenStatusStr.PHP_EOL);
        $this->log('======================='.PHP_EOL);
    }

    public function debugLibLog(string $dbgLabel, string $dbgMessage): void
    {
        $this->log(date('d.m.Y H:i:s').' | '.$dbgLabel.': '.$dbgMessage.PHP_EOL);
    }

    public function onUserPhoneChange(User $user, string $phone): void
    {
        $this->log("{$user->getPhone()} (id {$user->getUserId()}) phone changed to $phone");
    }

    public function onUserNameChange(User $user, string $username): void
    {
        $this->log("{$user->getUsername()} (id {$user->getUserId()}) username changed to $username");
    }
}
