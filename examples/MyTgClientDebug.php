<?php

use Client\AuthKey\AuthKeyCreator;
use Client\Client;
use Client\InfoObtainingClient\InfoClient;
use Client\InfoObtainingClient\Models\UserInfoModel;
use Client\StatusWatcherClient\Models\HiddenStatus;
use Client\StatusWatcherClient\Models\ImportResult;
use Client\StatusWatcherClient\Models\User;
use Client\StatusWatcherClient\StatusWatcherCallbacks;
use Client\StatusWatcherClient\StatusWatcherClient;
use Exception\TGException;
use Logger\ClientDebugLogger;
use Logger\Logger;
use SocksProxyAsync\Proxy;

/**
 * Client base class
 * Uses two telegram, connections (infoClient, monitoringClient)
 * Requires files: first.authkey, second.authkey
 */
class MyTgClientDebug implements StatusWatcherCallbacks, ClientDebugLogger
{
    /**
     * @var StatusWatcherClient
     */
    protected $monitoringClient;
    /**
     * @var InfoClient
     */
    protected $infoClient;
    /**
     * @var string
     */
    private $authKeyForFirstClient;
    /**
     * @var string
     */
    private $authKeyForSecondClient;
    /**
     * @var Proxy
     */
    private $proxy;

    /**
     * @param Proxy|null $proxy
     *
     * @throws TGException
     */
    public function __construct(?Proxy $proxy = null)
    {
        /*
         * Set TL-node logger
         */
        Logger::setupLogger($this);

        /*
         * (!) Authkeys can be the same (StatusClient и InfoClient), but it is NOT recommended,
         * due to Telegram-сервер sending nodes to different clients,leading to
     * data losses on clients.
         */
        $this->authKeyForFirstClient = trim(file_get_contents(__DIR__.'/first.authkey'));
        $this->authKeyForSecondClient = trim(file_get_contents(__DIR__.'/second.authkey'));

        /*
         * Clients init
         */
        $this->monitoringClient = new StatusWatcherClient($this);
        $this->infoClient = new InfoClient();
        $this->proxy = $proxy;
    }

    /**
     * @throws TGException
     */
    public function startActions()
    {
        $this->getContactsInfo();
        $this->pollAndTerminate();
        $this->monitorPhones();
    }

    /**
     * @throws TGException
     */
    private function monitorPhones()
    {
        $authKey = AuthKeyCreator::createFromString($this->authKeyForFirstClient);
        $this->monitoringClient->login($authKey, $this->proxy);

        /* add via phone numbers */
        $monitoringPhones = [
            '541123683798',
            '60192003400',
            '77779479694',
            '393475723072',
            '989196190933',
        ];
        $lastContactsCleaningTime = 0;
        $this->monitoringClient->reloadNumbers($monitoringPhones, function (ImportResult $result) use (&$lastContactsCleaningTime) {
            $lastContactsCleaningTime = time();
            echo 'Contacts imported total:'.count($result->importedPhones)."\n";
            echo 'Replaced phones:'.print_r($result->replacedPhones, true)."\n";
        });

        // wait a little between operations in order to get possible exceptions
        // it is preferable only once after first call of import/add contacts
        for($i = 0; $i < 10; $i++) $this->pollClientCycle($this->monitoringClient);

        /* add via user names */
        $this->monitoringClient->addUser('asen_17', function (bool $addResult) {});
        $this->monitoringClient->addUser('d_push', function (bool $addResult) {});

        $start = time();

        while(true){

            $this->pollClientCycle($this->monitoringClient);

            if(time() - $start > 10000000) {
                $this->terminateMonitoringClient();
                break;
            }

            if($lastContactsCleaningTime > 0 && time() - $lastContactsCleaningTime > 5) {
                // remove contact by name
                $this->monitoringClient->delUser('ASEN_17', function () {});
                $lastContactsCleaningTime = time();
            }
        }

        $this->monitoringClient->cleanMonitoringBook(function () {
            echo "Contacts cleaned\n";
        });

    }

    /**
     * @param Client $client
     *
     * @throws TGException
     */
    private function pollClientCycle(Client $client)
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

    /**
     * @throws TGException
     */
    private function terminateMonitoringClient()
    {
        $this->monitoringClient->terminate();
    }

    /**
     * @throws TGException
     */
    protected function getContactsInfo()
    {
        $phones = [
            '+79153801634',
        ];

        $this->infoLogin();

        /* info by username */
        $this->infoClient->getInfoByUsername('asen_17', true, true, function ($userInfoModel) {
            if ($userInfoModel->photo)
                file_put_contents(
                    $userInfoModel->username.'.'.$userInfoModel->photo->format,
                    $userInfoModel->photo->bytes
                );
        });

        $this->parseNumbers($phones, true, true);
    }

    /**
     * @param string[]      $numbers
     * @param bool          $withPhoto
     * @param bool          $largePhoto
     * @param callable|null $callback   function(UserInfoModel[])
     *
     * @throws TGException
     */
    public function parseNumbers(array $numbers, bool $withPhoto = false, bool $largePhoto = false, ?callable $callback = null)
    {
        $counter = count($numbers);
        $models = [];
        /* info by phone */
        foreach ($numbers as $phone) {
            $this->infoClient->getInfoByPhone($phone, $withPhoto, $largePhoto, function (?UserInfoModel $userInfoModel) use (&$counter, $callback, &$models) {
                if ($userInfoModel) {
                    if (!$callback) {
                        if ($userInfoModel->photo)
                            file_put_contents(
                                $userInfoModel->phone.'.'.$userInfoModel->photo->format,
                                $userInfoModel->photo->bytes
                            );
                        echo "#################################\n";
                        if ($userInfoModel->photo) {
                            $userInfoModel->photo->bytes = 'HIDDEN';
                        }
                        Logger::log(__CLASS__, print_r($userInfoModel, true));
                    } else {
                        $counter--;
                        $models[] = $userInfoModel;
                    }
                } else {
                    $counter--;
                }

                if (!$counter && $callback) {
                    $callback($models);
                }
            });
        }
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

    /**
     * Fetch messages and terminate client
     *
     * @throws TGException
     */
    public function pollAndTerminate(): void
    {
        $lastMsg = time();
        while (true) {

            if ($this->infoClient->pollMessage()) {
                $lastMsg = time();
            }

            if (time() - $lastMsg > 5)
                break;

            usleep(10000);
        }

        $this->infoClient->terminate();
    }

    /**
     * Connect to telegram with info (second) account
     *
     * @throws TGException
     */
    public function infoLogin(): void
    {
        $authKey = AuthKeyCreator::createFromString($this->authKeyForSecondClient);
        $this->infoClient->login($authKey, $this->proxy);
    }
}
