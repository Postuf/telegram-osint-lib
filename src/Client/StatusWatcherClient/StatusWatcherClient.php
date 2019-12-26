<?php

namespace Client\StatusWatcherClient;

use Client\AuthKey\AuthKey;
use Client\BasicClient\BasicClient;
use Client\BasicClient\BasicClientImpl;
use Client\PeriodicClient;
use Client\StatusMonitoringClient;
use Client\StatusWatcherClient\Models\HiddenStatus;
use Client\StatusWatcherClient\Models\ImportResult;
use Client\StatusWatcherClient\Models\User;
use Exception\TGException;
use MTSerialization\AnonymousMessage;
use SocksProxyAsync\Proxy;
use TGConnection\SocketMessenger\MessageListener;
use TLMessage\TLMessage\ServerMessages\Contact\ContactUser;
use TLMessage\TLMessage\ServerMessages\Contact\ImportedContacts;
use Tools\Phone;

class StatusWatcherClient implements StatusMonitoringClient, PeriodicClient, StatusWatcherCallbacksMiddleware, MessageListener
{
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

    /**
     * @param StatusWatcherCallbacks $callbacks
     *
     * @throws TGException
     */
    public function __construct(StatusWatcherCallbacks $callbacks)
    {
        $this->userCallbacks = $callbacks;
        $this->currentlyOnlineUsers = [];
        $this->currentlyOfflineUsers = [];

        $this->basicClient = new BasicClientImpl();
        $this->basicClient->setMessageListener($this);
        $this->messageAnalyzer = new StatusWatcherAnalyzer($this);
        $this->contactKeeper = new ContactsKeeper($this->basicClient);
    }

    /**
     * @param AuthKey $authKey
     * @param Proxy|null $proxy
     * @param callable|null $cb
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
     * @throws TGException
     *
     * @return void
     */
    protected function throwIfNotLoggedIn()
    {
        $this->basicClient->throwIfNotLoggedIn();
    }

    /**
     * @throws TGException
     *
     * @return bool
     */
    public function pollMessage()
    {
        $this->onPeriodAvailable();

        return $this->basicClient->pollMessage();
    }

    /**
     * @throws TGException
     *
     * @return void
     */
    public function onPeriodAvailable(): void
    {
        $this->checkOnlineStatusesExpired();
    }

    /**
     * @throws TGException
     */
    protected function checkOnlineStatusesExpired()
    {
        foreach ($this->currentlyOnlineUsers as $userId => $expires) {
            if (time() > $expires)
                $this->onUserOffline($userId, $expires);
        }
    }

    /**
     * @param array    $numbers
     * @param callable $onComplete
     *
     * @throws TGException
     */
    public function addNumbers(array $numbers, callable $onComplete)
    {
        $this->throwIfNotLoggedIn();
        $this->contactKeeper->addNumbers($numbers, $onComplete);
    }

    /**
     * @param array    $numbers
     * @param callable $onComplete
     *
     * @throws TGException
     */
    public function reloadNumbers(array $numbers, callable $onComplete)
    {
        $this->throwIfNotLoggedIn();
        $this->contactKeeper->getCurrentContacts(function (array $contacts) use ($numbers, $onComplete) {

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
     * @param callable $onComplete
     *
     * @throws TGException
     */
    public function delNumbers(array $numbers, callable $onComplete)
    {
        $this->throwIfNotLoggedIn();
        $this->contactKeeper->delNumbers($numbers, function () use ($onComplete) {
            $this->currentlyOnlineUsers = [];
            $this->currentlyOfflineUsers = [];
            $onComplete();
        });
    }

    /**
     * @param string   $userName
     * @param callable $onComplete
     *
     * @throws TGException
     */
    public function addUser(string $userName, callable $onComplete)
    {
        $this->throwIfNotLoggedIn();
        $this->contactKeeper->addUser($userName, $onComplete);
    }

    /**
     * @param string   $userName
     * @param callable $onComplete
     *
     * @throws TGException
     */
    public function delUser(string $userName, callable $onComplete)
    {
        $this->throwIfNotLoggedIn();
        $this->contactKeeper->delUser($userName, function () use ($onComplete) {
            $onComplete();
        });
    }

    /**
     * @param callable $onComplete
     *
     * @throws TGException
     */
    public function cleanMonitoringBook(callable $onComplete)
    {
        $this->throwIfNotLoggedIn();
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
     *
     * @throws TGException
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
     *
     * @throws TGException
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
     * @throws TGException
     *
     * @return void
     */
    public function terminate()
    {
        $this->basicClient->terminate();
    }
}
