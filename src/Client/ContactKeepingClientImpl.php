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
    /** @var BasicClient */
    protected $basicClient;
    /**
     * @var ContactsKeeper
     */
    protected $contactsKeeper;

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
     * @param array    $numbers
     * @param callable $onComplete function()
     *
     * @throws TGException
     */
    public function delNumbers(array $numbers, callable $onComplete): void
    {
        $this->throwIfNotLoggedIn(__METHOD__);
        $this->contactsKeeper->delNumbers($numbers, static function () use ($onComplete) {
            $onComplete();
        });
    }

    /**
     * @param array    $numbers
     * @param array    $usernames
     * @param callable $onComplete
     *
     * @throws TGException
     * @noinspection DuplicatedCode
     */
    public function reloadContacts(array $numbers, array $usernames, callable $onComplete): void
    {
        $this->throwIfNotLoggedIn(__METHOD__);
        $this->contactsKeeper->getCurrentContacts(ReloadContactsHandler::getHandler($this, $numbers, $usernames, $onComplete));
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
