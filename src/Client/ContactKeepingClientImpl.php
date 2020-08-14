<?php

declare(strict_types=1);

namespace TelegramOSINT\Client;

use TelegramOSINT\Client\BasicClient\BasicClient;
use TelegramOSINT\Client\Helpers\ReloadContactsHandler;
use TelegramOSINT\Client\StatusWatcherClient\ContactsKeeper;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\Tools\Clock;

abstract class ContactKeepingClientImpl extends DeferredClient implements ContactKeepingClient
{
    private const ADD_USER_PAUSE_SECONDS = 1;
    /** @var int */
    private int $lastUsedAddedTime = 0;
    /** @var int */
    private int $userAddQueueSize = 0;

    /** @var BasicClient */
    protected BasicClient $basicClient;
    protected ContactsKeeper $contactsKeeper;

    public function __construct(?Clock $clock, BasicClient $basicClient, array $startContacts = [])
    {
        parent::__construct($clock);
        $this->basicClient = $basicClient;
        $this->contactsKeeper = new ContactsKeeper($this->basicClient, $startContacts);
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
     * @param string   $userName
     * @param callable $onComplete function(bool)
     *
     * @throws TGException
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
     * @param array    $numbers
     * @param array    $users
     * @param callable $onComplete function()
     *
     * @throws TGException
     */
    public function delNumbersAndUsers(array $numbers, array $users, callable $onComplete): void
    {
        $this->throwIfNotLoggedIn(__METHOD__);
        $this->contactsKeeper->delNumbersAndUsers($numbers, $users, static function () use ($onComplete) {
            $onComplete();
        });
    }

    /**
     * @param array    $numbers
     * @param array    $usernames
     * @param callable $onComplete
     *
     * @throws TGException
     */
    public function reloadContacts(array $numbers, array $usernames, callable $onComplete): void
    {
        $this->throwIfNotLoggedIn(__METHOD__);
        $this->contactsKeeper->getCurrentContacts(ReloadContactsHandler::getHandler($this, $numbers, $usernames, $onComplete));
    }

    /**
     * @param string   $number
     * @param callable $onComplete
     */
    public function getContactByPhone(string $number, callable $onComplete): void
    {
        $this->contactsKeeper->getUserByPhone($number, $onComplete);
    }

    /**
     * @param callable $onComplete
     *
     * @throws TGException
     */
    public function cleanContactsBook(callable $onComplete): void
    {
        $this->throwIfNotLoggedIn(__METHOD__);
        $this->contactsKeeper->cleanContacts($onComplete);
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
}
