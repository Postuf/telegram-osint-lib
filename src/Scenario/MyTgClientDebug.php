<?php

namespace TelegramOSINT\Scenario;

use TelegramOSINT\Client\AuthKey\AuthKeyCreator;
use TelegramOSINT\Client\Client;
use TelegramOSINT\Client\InfoObtainingClient\InfoClient;
use TelegramOSINT\Client\InfoObtainingClient\Models\UserInfoModel;
use TelegramOSINT\Client\StatusWatcherClient\Models\HiddenStatus;
use TelegramOSINT\Client\StatusWatcherClient\Models\ImportResult;
use TelegramOSINT\Client\StatusWatcherClient\Models\User;
use TelegramOSINT\Client\StatusWatcherClient\StatusWatcherCallbacks;
use TelegramOSINT\Client\StatusWatcherClient\StatusWatcherClient;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\Logger\ClientDebugLogger;
use TelegramOSINT\Logger\Logger;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Contact\ContactUser;
use TelegramOSINT\Tools\Proxy;

/**
 * Client base class
 *
 * Uses two telegram, connections (infoClient, monitoringClient).
 *
 * Requires files: `first.authkey`, `second.authkey`.
 */
class MyTgClientDebug implements StatusWatcherCallbacks, ClientDebugLogger, ScenarioInterface
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
    /** @var float */
    private $timeout = 5.0;

    /**
     * @param Proxy|null                    $proxy
     * @param ClientGeneratorInterface|null $generator
     *
     * @throws TGException
     */
    public function __construct(?Proxy $proxy = null, ?ClientGeneratorInterface $generator = null)
    {
        /*
         * Set TL-node logger
         */
        Logger::setupLogger($this);

        if (!$generator) {
            $generator = new ClientGenerator();
        }

        /*
         * (!) Authkeys can be the same (StatusClient и InfoClient), but it is NOT recommended,
         * due to Telegram-server sending nodes to different clients,leading to
         * data losses on clients.
         */
        $this->authKeyForFirstClient = $generator->getAuthKeyInfo();
        $this->authKeyForSecondClient = $generator->getAuthKeyStatus();

        /*
         * Clients init
         */
        $this->monitoringClient = $generator->getStatusWatcherClient($this);
        $this->infoClient = $generator->getInfoClient();
        $this->proxy = $proxy;
    }

    /**
     * @param bool $pollAndTerminate
     *
     * @throws TGException
     */
    public function startActions(bool $pollAndTerminate = true): void
    {
        $this->getContactsInfo();
        if ($pollAndTerminate) {
            $this->pollAndTerminate();
            $this->monitorPhones();
        }
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
     */
    public function parseNumbers(array $numbers, bool $withPhoto = false, bool $largePhoto = false, ?callable $onComplete = null)
    {
        $counter = count($numbers);
        $models = [];
        $this->infoClient->reloadNumbers($numbers, function (ImportResult $result) use (&$models, $onComplete, $withPhoto, $largePhoto) {
            $loadFlags = count($result->importedPhones);

            foreach ($result->importedPhones as $importedPhone) {
                $this->infoClient->getContactByPhone($importedPhone, function (ContactUser $user) use (&$models, &$loadFlags, $onComplete, $withPhoto, $largePhoto) {
                    $model = new UserInfoModel();
                    $model->id = $user->getUserId();
                    $model->phone = $user->getPhone();
                    $model->langCode = $user->getLangCode();
                    $model->firstName = $user->getFirstName();
                    $model->lastName = $user->getLastName();
                    $model->username = $user->getUsername();

                    $this->infoClient->getFullUserInfo($user, $withPhoto, $largePhoto, function (UserInfoModel $fullModel) use ($model, &$models, $user, &$loadFlags, $onComplete) {
                        $model->commonChatsCount = $fullModel->commonChatsCount;
                        $model->status = $fullModel->status;
                        $model->bio = $fullModel->bio;

                        $models[$user->getUserId()] = $model;
                        $loadFlags--;

                        if ($loadFlags == 0) {
                            $this->reloadUsersInfo($models, $onComplete);
                        }
                    });
                });
                sleep(2);
            }

        });

        /* info by phone */
 /*       foreach ($numbers as $phone) {
            $this->infoClient->getInfoByPhone($phone, $withPhoto, $largePhoto, function (?UserInfoModel $userInfoModel) use (&$counter, $callback, &$models, $phone) {
                if ($userInfoModel) {
                    $userInfoModel->phone = $phone;
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
            // sleep to avoid flood err
            sleep(2);
        } */
    }

    private function reloadUsersInfo(array $models, callable $onComplete)
    {
        $this->infoClient->cleanContacts(function () use (&$models, $onComplete) {
            foreach ($models as $user) {
                if ($user->username) {
                    $this->infoClient->getInfoByUsername($user->username, true, true, function (UserInfoModel $userModel) use (&$models, $onComplete) {
                        $userModel->phone = $models[$userModel->id]->phone;
                        $userModel->bio = $models[$userModel->id]->bio;
                        $userModel->commonChatsCount = $models[$userModel->id]->commonChatsCount;
                        $onComplete($userModel);
                    });
                } else {
                    $user->firstName = '----';
                    $user->lastName = '----';
                    $user->username = '----';
                    $onComplete($user);
                }
                sleep(1);
            }
        });
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

    public function setTimeout(float $timeout): void
    {
        $this->timeout = $timeout;
    }

    /**
     * Fetch messages and terminate client
     *
     * @param float $timeout seconds
     *
     * @throws TGException
     */
    public function pollAndTerminate(float $timeout = 0.0): void
    {
        if ($timeout == 0.0) {
            $timeout = $this->timeout;
        }
        $lastMsg = microtime(true);
        while (true) {

            if ($this->infoClient->pollMessage()) {
                $lastMsg = microtime(true);
            }

            if (microtime(true) - $lastMsg > $timeout)
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
        if (!$this->infoClient->isLoggedIn()) {
            $this->infoClient->login($authKey, $this->proxy);
        }
    }
}
