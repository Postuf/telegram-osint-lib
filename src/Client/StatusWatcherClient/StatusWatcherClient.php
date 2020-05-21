<?php

namespace TelegramOSINT\Client\StatusWatcherClient;

use TelegramOSINT\Client\AuthKey\AuthKey;
use TelegramOSINT\Client\BasicClient\BasicClient;
use TelegramOSINT\Client\BasicClient\BasicClientImpl;
use TelegramOSINT\Client\PeriodicClient;
use TelegramOSINT\Client\StatusMonitoringClient;
use TelegramOSINT\Client\StatusWatcherClient\Models\HiddenStatus;
use TelegramOSINT\Client\StatusWatcherClient\Models\ImportResult;
use TelegramOSINT\Client\StatusWatcherClient\Models\User;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\LibConfig;
use TelegramOSINT\Logger\ClientDebugLogger;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TGConnection\SocketMessenger\MessageListener;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Contact\ContactUser;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Contact\ImportedContacts;
use TelegramOSINT\Tools\Phone;
use TelegramOSINT\Tools\Proxy;

class StatusWatcherClient implements StatusMonitoringClient, PeriodicClient, StatusWatcherCallbacksMiddleware, MessageListener
{
    private const RELOAD_CONTACTS_EVERY_SECONDS = 20;

    /**
     * @var BasicClient
     */
    private $basicClient;
    /**
     * @var StatusWatcherAnalyzer
     */
    private $messageAnalyzer;
    /**
     * @var StatusWatcherCallbacks
     */
    private $userCallbacks;
    /**
     * @var ContactsKeeper
     */
    protected $contactKeeper;
    /**
     * @var array
     *            Format: id=>expires
     */
    private $currentlyOnlineUsers;
    /**
     * @var array
     *            Format: id=>id
     */
    private $currentlyOfflineUsers;
    /** @var ClientDebugLogger|null */
    private $logger;
    /** @var int */
    private $lastContactsReloaded = 0;

    /**
     * @param StatusWatcherCallbacks $callbacks
     * @param ClientDebugLogger|null $logger
     *
     * @throws TGException
     */
    public function __construct(StatusWatcherCallbacks $callbacks, ?ClientDebugLogger $logger = null)
    {
        $this->basicClient = new BasicClientImpl(
            LibConfig::CONN_SOCKET_PROXY_TIMEOUT_SEC,
            $logger
        );
        $this->userCallbacks = $callbacks;
        $this->currentlyOnlineUsers = [];
        $this->currentlyOfflineUsers = [];

        $this->basicClient->setMessageListener($this);
        $this->messageAnalyzer = new StatusWatcherAnalyzer($this);
        $this->contactKeeper = new ContactsKeeper($this->basicClient);
        $this->logger = $logger;
    }

    /**
     * @param AuthKey       $authKey
     * @param Proxy         $proxy
     * @param callable|null $cb      function()
     *
     * @throws TGException
     *
     * @return void
     */
    public function login(AuthKey $authKey, Proxy $proxy = null, ?callable $cb = null)
    {
        $this->basicClient->login($authKey, $proxy, $cb);
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->basicClient->isLoggedIn();
    }

    /**
     * @param string $message
     *
     * @throws TGException
     *
     * @return void
     */
    protected function throwIfNotLoggedIn(string $message)
    {
        $this->basicClient->throwIfNotLoggedIn($message);
    }

    /**
     * @throws TGException
     *
     * @return bool
     */
    public function pollMessage()
    {
        $this->onPeriodAvailable();
        $this->reloadContactsIfNeeded();

        return $this->basicClient->pollMessage();
    }

    private function reloadContactsIfNeeded(): void
    {
        $time = time();
        if ($time > $this->lastContactsReloaded + self::RELOAD_CONTACTS_EVERY_SECONDS) {
            $this->contactKeeper->reloadCurrentContacts(function () {});
            $this->lastContactsReloaded = $time;
        }
    }

    public function onPeriodAvailable(): void
    {
        $this->checkOnlineStatusesExpired();
    }

    protected function checkOnlineStatusesExpired(): void
    {
        foreach ($this->currentlyOnlineUsers as $userId => $expires) {
            if (time() > $expires)
                $this->onUserOffline($userId, $expires);
        }
    }

    /**
     * @param array    $numbers
     * @param callable $onComplete function(ImportResult $result)
     *
     * @throws TGException
     */
    public function addNumbers(array $numbers, callable $onComplete)
    {
        $this->throwIfNotLoggedIn(__METHOD__);
        $this->contactKeeper->addNumbers($numbers, $onComplete);
    }

    /**
     * @param array    $numbers
     * @param callable $onComplete function(ImportResult $result)
     *
     * @throws TGException
     */
    public function reloadNumbers(array $numbers, callable $onComplete)
    {
        $this->throwIfNotLoggedIn(__METHOD__);
        $this->lastContactsReloaded = time();
        $this->contactKeeper->reloadCurrentContacts(function (array $contacts) use ($numbers, $onComplete) {

            $currentPhones = [];
            $currentUserNames = [];

            /** @var ContactUser[] $contacts */
            foreach ($contacts as $contact){
                if($contact->getPhone())
                    $currentPhones[] = Phone::convertToTelegramView($contact->getPhone());
                if($contact->getUsername())
                    $currentUserNames[] = $contact->getUsername();
            }

            foreach ($numbers as $key => $number){
                $numbers[$key] = Phone::convertToTelegramView($number);
            }

            $existingNumbers = array_intersect($currentPhones, $numbers);
            $obsoleteNumbers = array_diff($currentPhones, $numbers);
            $newNumbers = array_diff($numbers, $currentPhones);

            $addNumbersFunc = function () use ($newNumbers, $onComplete, $existingNumbers) {
                if (!empty($newNumbers)) {
                    $this->addNumbers($newNumbers, function (ImportResult $result) use ($onComplete, $existingNumbers) {
                        $result->importedPhones = array_merge($result->importedPhones, $existingNumbers);
                        $onComplete($result);
                    });
                } else {
                    $importResult = new ImportResult();
                    $importResult->importedPhones = $existingNumbers;
                    $onComplete($importResult);
                }
            };

            if(!empty($obsoleteNumbers)) {
                $this->delNumbers($obsoleteNumbers, function () use ($addNumbersFunc) { $addNumbersFunc(); });
            } else {
                $addNumbersFunc();
            }
        });
    }

    /**
     * @param array    $numbers
     * @param callable $onComplete function()
     *
     * @throws TGException
     */
    public function delNumbers(array $numbers, callable $onComplete)
    {
        $this->throwIfNotLoggedIn(__METHOD__);
        $this->contactKeeper->delNumbers($numbers, function () use ($onComplete) {
            $this->currentlyOnlineUsers = [];
            $this->currentlyOfflineUsers = [];
            $onComplete();
        });
    }

    /**
     * @param string   $userName
     * @param callable $onComplete function(bool)
     *
     * @throws TGException
     */
    public function addUser(string $userName, callable $onComplete)
    {
        $this->throwIfNotLoggedIn(__METHOD__);
        $this->contactKeeper->addUser($userName, $onComplete);
    }

    /**
     * @param string   $userName
     * @param callable $onComplete function()
     *
     * @throws TGException
     */
    public function delUser(string $userName, callable $onComplete)
    {
        $this->throwIfNotLoggedIn(__METHOD__);
        $this->contactKeeper->delUser($userName, function () use ($onComplete) {
            $onComplete();
        });
    }

    /**
     * @param callable $onComplete function()
     *
     * @throws TGException
     */
    public function cleanMonitoringBook(callable $onComplete)
    {
        $this->throwIfNotLoggedIn(__METHOD__);
        $this->contactKeeper->cleanContacts($onComplete);
    }

    /**
     * @param AnonymousMessage $message
     *
     * @throws TGException
     */
    public function onMessage(AnonymousMessage $message)
    {
        $this->messageAnalyzer->analyzeMessage($message);
    }

    /**
     * @param int $userId
     * @param int $expires
     *
     * @throws TGException
     */
    public function onUserOnline(int $userId, int $expires)
    {
        if(($expires - time()) / 60 > 25)
            throw new TGException(TGException::ERR_ASSERT_UPDATE_EXPIRES_TIME_LONG, 'userId: '.$userId.'; (expires-now) sec: '.($expires - time()));
        $isUserStillOnline = in_array($userId, array_keys($this->currentlyOnlineUsers));
        unset($this->currentlyOfflineUsers[$userId]);
        $this->currentlyOnlineUsers[$userId] = $expires;

        // notification for user
        if(!$isUserStillOnline){
            $this->contactKeeper->getUserById($userId, function ($user) use ($userId, $expires) {
                // arbitrary user
                if(!($user instanceof ContactUser))
                    return;

                $phone = $user->getPhone();
                $userName = $user->getUsername();

                if($phone || $userName)
                    $this->userCallbacks->onUserOnline(new User($phone, $userName), $expires);
                else
                    throw new TGException(TGException::ERR_ASSERT_UPDATE_USER_UNIDENTIFIED, 'userId: '.$userId.'; userObj='.print_r($user, true));
            });
        }
    }

    /**
     * @param int $userId
     * @param int $wasOnline
     */
    public function onUserOffline(int $userId, int $wasOnline)
    {
        $isUserOffline = in_array($userId, $this->currentlyOfflineUsers);
        unset($this->currentlyOnlineUsers[$userId]);
        $this->currentlyOfflineUsers[$userId] = $userId;

        // notification for user
        if(!$isUserOffline) {
            $this->contactKeeper->getUserById($userId, function ($user) use ($userId, $wasOnline) {
                // arbitrary user
                if(!($user instanceof ContactUser))
                    return;

                $phone = $user->getPhone();
                $userName = $user->getUsername();

                if($phone || $userName)
                    $this->userCallbacks->onUserOffline(new User($phone, $userName), $wasOnline);
                else
                    throw new TGException(TGException::ERR_ASSERT_UPDATE_USER_UNIDENTIFIED, 'userId: '.$userId.'; userObj='.print_r($user, true));
            });
        }
    }

    /**
     * @param int          $userId
     * @param HiddenStatus $hiddenStatusState
     */
    public function onUserHidStatus(int $userId, HiddenStatus $hiddenStatusState)
    {
        unset($this->currentlyOnlineUsers[$userId]);
        unset($this->currentlyOfflineUsers[$userId]);

        // notification for user
        $this->contactKeeper->getUserById($userId, function ($user) use ($userId, $hiddenStatusState) {
            // arbitrary user
            if(!($user instanceof ContactUser))
                return;

            $phone = $user->getPhone();
            $userName = $user->getUsername();

            if($phone || $userName)
                $this->userCallbacks->onUserHidStatus(new User($phone, $userName), $hiddenStatusState);
            else
                throw new TGException(TGException::ERR_ASSERT_UPDATE_USER_UNIDENTIFIED, 'userId: '.$userId.'; userObj='.print_r($user, true));
        });
    }

    /**
     * @param ImportedContacts $contactsObject
     *
     * @throws TGException
     */
    public function onContactsImported(ImportedContacts $contactsObject)
    {
        $importedPhones = [];

        foreach ($contactsObject->getImportedUsers() as $user) {

            try{
                if(!$user->getPhone())
                    throw new TGException(TGException::ERR_ASSERT_UPDATE_USER_UNIDENTIFIED);
            } catch (TGException $e){
                throw new TGException(TGException::ERR_ASSERT_UPDATE_USER_UNIDENTIFIED);
            }

            $importedPhones[] = $user->getPhone();
        }
    }

    /**
     * @return void
     */
    public function terminate()
    {
        $this->basicClient->terminate();
    }
}
