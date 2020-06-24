<?php

namespace TelegramOSINT\Client\StatusWatcherClient;

use TelegramOSINT\Client\AuthKey\AuthKey;
use TelegramOSINT\Client\BasicClient\BasicClient;
use TelegramOSINT\Client\BasicClient\BasicClientWithStatusReportingImpl;
use TelegramOSINT\Client\ContactKeepingClient;
use TelegramOSINT\Client\DeferredClient;
use TelegramOSINT\Client\Helpers\ReloadContactsHandler;
use TelegramOSINT\Client\PeriodicClient;
use TelegramOSINT\Client\StatusMonitoringClient;
use TelegramOSINT\Client\StatusWatcherClient\Models\HiddenStatus;
use TelegramOSINT\Client\StatusWatcherClient\Models\User;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\LibConfig;
use TelegramOSINT\Logger\ClientDebugLogger;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TGConnection\SocketMessenger\MessageListener;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Contact\ContactUser;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Contact\ImportedContacts;
use TelegramOSINT\Tools\Clock;
use TelegramOSINT\Tools\Proxy;

class StatusWatcherClient extends DeferredClient implements
    StatusMonitoringClient,
    PeriodicClient,
    StatusWatcherCallbacksMiddleware,
    MessageListener,
    ContactKeepingClient
{
    private const RELOAD_CONTACTS_EVERY_SECONDS = 20;
    private const ADD_USER_PAUSE_SECONDS = 1;

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
    protected $contactsKeeper;
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
    /** @var int */
    private $lastContactsReloaded = 0;
    /** @var int */
    private $lastUsedAddedTime = 0;
    /** @var int */
    private $userAddQueueSize = 0;

    /**
     * @param StatusWatcherCallbacks $callbacks
     * @param ClientDebugLogger|null $logger
     * @param ContactUser[]          $startContacts
     * @param Clock|null             $clock
     * @param BasicClient|null       $basicClient
     *
     * @throws TGException
     */
    public function __construct(
        StatusWatcherCallbacks $callbacks,
        ?ClientDebugLogger $logger = null,
        array $startContacts = [],
        ?Clock $clock = null,
        ?BasicClient $basicClient = null
    ) {
        parent::__construct($clock);
        $this->basicClient = $basicClient ?: new BasicClientWithStatusReportingImpl(
            LibConfig::CONN_SOCKET_PROXY_TIMEOUT_SEC,
            $logger
        );
        $this->userCallbacks = $callbacks;
        $this->currentlyOnlineUsers = [];
        $this->currentlyOfflineUsers = [];

        $this->basicClient->setMessageListener($this);
        $this->messageAnalyzer = new StatusWatcherAnalyzer($this);
        $this->contactsKeeper = new ContactsKeeper($this->basicClient, $startContacts);
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
    public function login(AuthKey $authKey, Proxy $proxy = null, ?callable $cb = null): void
    {
        $this->basicClient->login($authKey, $proxy, $cb);
    }

    public function isLoggedIn(): bool
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
    protected function throwIfNotLoggedIn(string $message): void
    {
        $this->basicClient->throwIfNotLoggedIn($message);
    }

    /**
     * @throws TGException
     *
     * @return bool
     */
    public function pollMessage(): bool
    {
        $this->onPeriodAvailable();
        $this->reloadContactsIfNeeded();
        $this->processDeferredQueue();

        return $this->basicClient->pollMessage();
    }

    private function reloadContactsIfNeeded(): void
    {
        $time = $this->clock->time();
        if ($time > $this->lastContactsReloaded + self::RELOAD_CONTACTS_EVERY_SECONDS) {
            $this->contactsKeeper->reloadCurrentContacts(static function () {});
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
            if ($this->clock->time() > $expires) {
                $this->onUserOffline($userId, $expires);
            }
        }
    }

    /**
     * @param array    $numbers
     * @param callable $onComplete function(ImportResult $result)
     *
     * @throws TGException
     */
    public function addNumbers(array $numbers, callable $onComplete): void
    {
        $this->throwIfNotLoggedIn(__METHOD__);
        $this->contactsKeeper->addNumbers($numbers, $onComplete);
    }

    /**
     * @param array    $numbers
     * @param callable $onComplete function(ImportResult $result)
     *
     * @throws TGException
     */
    public function reloadNumbers(array $numbers, callable $onComplete): void
    {
        $this->throwIfNotLoggedIn(__METHOD__);
        $this->lastContactsReloaded = $this->clock->time();
        $this->contactsKeeper->reloadCurrentContacts(ReloadContactsHandler::getHandler($this, $numbers, $onComplete));
    }

    /**
     * @param array    $numbers
     * @param callable $onComplete function()
     *
     * @throws TGException
     */
    public function delNumbers(array $numbers, callable $onComplete): void
    {
        $this->throwIfNotLoggedIn(__METHOD__);
        $this->contactsKeeper->delNumbers($numbers, function () use ($onComplete) {
            $this->currentlyOnlineUsers = [];
            $this->currentlyOfflineUsers = [];
            $onComplete();
        });
    }

    /**
     * @param string   $userName
     * @param callable $onComplete function(bool)
     */
    public function addUser(string $userName, callable $onComplete): void
    {
        $this->userAddQueueSize++;
        $cb = function () use ($userName, $onComplete) {
            $this->lastUsedAddedTime = $this->clock->time();
            $this->userAddQueueSize--;
            $this->throwIfNotLoggedIn(__METHOD__);
            $this->contactsKeeper->addUser($userName, $onComplete);
        };

        $time = $this->clock->time();
        if ($time - $this->lastUsedAddedTime >= self::ADD_USER_PAUSE_SECONDS) {
            $cb();
        } else {
            $this->defer($cb, max($this->userAddQueueSize, 1));
        }
    }

    /**
     * @param string[] $userNames
     * @param callable $onComplete function()
     *
     * @throws TGException
     */
    public function delUsers(array $userNames, callable $onComplete): void
    {
        $this->throwIfNotLoggedIn(__METHOD__);
        $this->contactsKeeper->delUsers($userNames, static function () use ($onComplete) {
            $onComplete();
        });
    }

    /**
     * @param array    $numbers
     * @param string[] $userNames
     * @param callable $onComplete function()
     *
     * @throws TGException
     */
    public function delNumbersAndUsers(array $numbers, array $userNames, callable $onComplete): void
    {
        $this->throwIfNotLoggedIn(__METHOD__);
        $this->contactsKeeper->delNumbersAndUsers($numbers, $userNames, static function () use ($onComplete) {
            $onComplete();
        });
    }

    /**
     * @param callable $onComplete function()
     *
     * @throws TGException
     */
    public function cleanMonitoringBook(callable $onComplete): void
    {
        $this->throwIfNotLoggedIn(__METHOD__);
        $this->contactsKeeper->cleanContacts($onComplete);
    }

    /**
     * @param AnonymousMessage $message
     *
     * @throws TGException
     */
    public function onMessage(AnonymousMessage $message): void
    {
        $this->messageAnalyzer->analyzeMessage($message);
    }

    /**
     * @param int $userId
     * @param int $expires
     *
     * @throws TGException
     */
    public function onUserOnline(int $userId, int $expires): void
    {
        if(($expires - $this->clock->time()) / 60 > 25) {
            throw new TGException(TGException::ERR_ASSERT_UPDATE_EXPIRES_TIME_LONG, 'userId: '.$userId.'; (expires-now) sec: '.($expires - $this->clock->time()));
        }
        $isUserStillOnline = array_key_exists($userId, $this->currentlyOnlineUsers);
        unset($this->currentlyOfflineUsers[$userId]);
        $this->currentlyOnlineUsers[$userId] = $expires;

        // notification for user
        if(!$isUserStillOnline){
            $this->contactsKeeper->getUserById($userId, function ($user) use ($userId, $expires) {
                // arbitrary user
                if(!($user instanceof ContactUser)) {
                    return;
                }

                $phone = $user->getPhone();
                $userName = $user->getUsername();

                if($phone || $userName) {
                    $this->userCallbacks->onUserOnline(new User($phone, $userName), $expires);
                } else {
                    throw new TGException(TGException::ERR_ASSERT_UPDATE_USER_UNIDENTIFIED, 'userId: '.$userId.'; userObj='.print_r($user, true));
                }
            });
        }
    }

    /**
     * @param int $userId
     * @param int $wasOnline
     */
    public function onUserOffline(int $userId, int $wasOnline): void
    {
        $isUserOffline = in_array($userId, $this->currentlyOfflineUsers, true);
        unset($this->currentlyOnlineUsers[$userId]);
        $this->currentlyOfflineUsers[$userId] = $userId;

        // notification for user
        if(!$isUserOffline) {
            $this->contactsKeeper->getUserById($userId, function ($user) use ($userId, $wasOnline) {
                // arbitrary user
                if(!($user instanceof ContactUser)) {
                    return;
                }

                $phone = $user->getPhone();
                $userName = $user->getUsername();

                if($phone || $userName) {
                    $this->userCallbacks->onUserOffline(new User($phone, $userName), $wasOnline);
                } else {
                    throw new TGException(TGException::ERR_ASSERT_UPDATE_USER_UNIDENTIFIED, 'userId: '.$userId.'; userObj='.print_r($user, true));
                }
            });
        }
    }

    /**
     * @param int          $userId
     * @param HiddenStatus $hiddenStatusState
     */
    public function onUserHidStatus(int $userId, HiddenStatus $hiddenStatusState): void
    {
        unset($this->currentlyOnlineUsers[$userId], $this->currentlyOfflineUsers[$userId]);

        // notification for user
        $this->contactsKeeper->getUserById($userId, function ($user) use ($userId, $hiddenStatusState) {
            // arbitrary user
            if(!($user instanceof ContactUser)) {
                return;
            }

            $phone = $user->getPhone();
            $userName = $user->getUsername();

            if($phone || $userName) {
                $this->userCallbacks->onUserHidStatus(new User($phone, $userName), $hiddenStatusState);
            } else {
                throw new TGException(TGException::ERR_ASSERT_UPDATE_USER_UNIDENTIFIED, 'userId: '.$userId.'; userObj='.print_r($user, true));
            }
        });
    }

    /**
     * @param ImportedContacts $contactsObject
     *
     * @throws TGException
     */
    public function onContactsImported(ImportedContacts $contactsObject): void
    {
        foreach ($contactsObject->getImportedUsers() as $user) {
            if(!$user->getPhone()) {
                throw new TGException(TGException::ERR_ASSERT_UPDATE_USER_UNIDENTIFIED);
            }
        }
    }

    /**
     * @return void
     */
    public function terminate(): void
    {
        $this->basicClient->terminate();
    }

    public function onUserPhoneChange(int $userId, string $phone): void
    {
        $this->contactsKeeper->getUserById($userId, function ($user) {
            // arbitrary user
            if (!($user instanceof ContactUser)) {
                return;
            }

            $phone = $user->getPhone();
            $userName = $user->getUsername();

            if (!empty($phone)) {
                $this->contactsKeeper->updatePhone($user->getUserId(), $user->getPhone());
            }
            $this->userCallbacks->onUserPhoneChange(new User($phone, $userName, $user->getUserId()), $phone);
        });
    }

    public function onUserNameChange(int $userId, string $username): void
    {
        $this->contactsKeeper->getUserById($userId, function ($user) {
            // arbitrary user
            if (!($user instanceof ContactUser)) {
                return;
            }

            $phone = $user->getPhone();
            $userName = $user->getUsername();

            $this->contactsKeeper->updateUsername($user->getUserId(), $user->getUsername());
            $this->userCallbacks->onUserNameChange(new User($phone, $userName, $user->getUserId()), $userName);
        });
    }

    public function hasDeferredCalls(): bool
    {
        return parent::hasDeferredCalls();
    }

    /**
     * @return ContactUser[]
     */
    public function getCurrentContacts(): array
    {
        return $this->contactsKeeper->getContacts();
    }
}
