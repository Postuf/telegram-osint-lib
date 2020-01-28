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
use TelegramOSINT\Logger\ClientDebugLogger;
use TelegramOSINT\Logger\Logger;
use TelegramOSINT\Tools\Proxy;

/**
 * Status watcher client
 *
 * Subscribes to a bunch of accounts and then
 */
class StatusWatcherScenario implements StatusWatcherCallbacks, ClientDebugLogger, ScenarioInterface
{
    /**
     * @var StatusWatcherClient
     */
    protected $client;
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

    /**
     * @param string[]                      $numbers
     * @param array                         $users
     * @param ClientGeneratorInterface|null $generator
     * @param Proxy|null                    $proxy
     *
     * @throws TGException
     */
    public function __construct(
        array $numbers,
        array $users = [],
        ?ClientGeneratorInterface $generator = null,
        ?Proxy $proxy = null
    ) {
        Logger::setupLogger($this);

        if (!$generator) {
            $generator = new ClientGenerator();
        }

        $this->authKey = $generator->getAuthKey();
        $this->numbers = $numbers;
        $this->users = $users;

        $this->client = $generator->getStatusWatcherClient($this);
        $this->proxy = $proxy;
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
        $this->client->reloadNumbers($monitoringPhones, function (ImportResult $result) use (&$lastContactsCleaningTime) {
            $lastContactsCleaningTime = time();
            echo 'Contacts imported total:'.count($result->importedPhones)."\n";
            echo 'Replaced phones:'.print_r($result->replacedPhones, true)."\n";
        });

        // wait a little between operations in order to get possible exceptions
        // it is preferable only once after first call of import/add contacts
        for($i = 0; $i < 10; $i++) $this->pollClientCycle($this->client);

        /* add via user names */
        foreach ($this->users as $user) {
            $this->client->addUser($user, function (bool $addResult) {
            });
        }

        $start = time();

        while(true){

            $this->pollClientCycle($this->client);

            if(time() - $start > 10000000) {
                $this->client->terminate();
                break;
            }

            if($lastContactsCleaningTime > 0 && time() - $lastContactsCleaningTime > 5) {
                // remove contact by name
                $this->client->delUser('ASEN_17', function () {});
                $lastContactsCleaningTime = time();
            }
        }

        $this->client->cleanMonitoringBook(function () {
            echo "Contacts cleaned\n";
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
            if($e->getCode() == TGException::ERR_CLIENT_ADD_USERNAME_ALREADY_IN_ADDRESS_BOOK)

                echo 'Error: '.$e->getMessage().PHP_EOL;
            else
                throw $e;
        }

        // save some CPU in infinite cycles
        usleep(50000);
    }

    public function onUserOnline(User $user, int $expires)
    {
        echo "=======================\n";
        echo 'User '.$user->getPhone().'|'.$user->getUsername().' now online. Expires= '.date('d/m/Y H:i:s', $expires)."\n";
        echo "=======================\n";
    }

    public function onUserOffline(User $user, int $wasOnline)
    {
        echo "=======================\n";
        echo 'User '.$user->getPhone().'|'.$user->getUsername().' now offline. Last seen = '.date('d/m/Y H:i:s', $wasOnline)."\n";
        echo "=======================\n";
    }

    /**
     * @param User         $user
     * @param HiddenStatus $hiddenStatusState
     */
    public function onUserHidStatus(User $user, HiddenStatus $hiddenStatusState)
    {
        switch ($hiddenStatusState){
            case HiddenStatus::HIDDEN_SEEN_LAST_MONTH:
                $hiddenStatusState = 'last month';
                break;
            case HiddenStatus::HIDDEN_SEEN_LAST_WEEK:
                $hiddenStatusState = 'last week';
                break;
            case HiddenStatus::HIDDEN_SEEN_RECENTLY:
                $hiddenStatusState = 'today or yesterday';
                break;
            case HiddenStatus::HIDDEN_EMPTY:
                $hiddenStatusState = 'unknown';
                break;
            case HiddenStatus::HIDDEN_SEEN_LONG_AGO:
                $hiddenStatusState = 'long ago';
                break;
        }

        echo "=======================\n";
        echo 'User '.$user->getPhone().'/'.$user->getUsername()." hid his status\n";
        echo 'Hidden status info: '.$hiddenStatusState."\n";
        echo "=======================\n";
    }

    public function debugLibLog(string $dbgLabel, string $dbgMessage)
    {
        echo date('d.m.Y H:i:s').' | '.$dbgLabel.': '.$dbgMessage."\n";
    }
}
