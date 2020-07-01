<?php

/** @noinspection PhpUnused */

namespace TelegramOSINT\Client;

interface StatusMonitoringClient extends Client
{
    /**
     * Adds single number in monitoring book
     *
     * @param string[] $numbers
     * @param callable $onComplete function(ImportResult $result)
     * @noinspection PhpUnused
     */
    public function addNumbers(array $numbers, callable $onComplete): void;

    /**
     * Leaves only $numbers in monitoring book, removes any other contacts
     *
     * @param string[] $numbers
     * @param array    $usernames
     * @param callable $onComplete function(ImportResult $result)
     */
    public function reloadContacts(array $numbers, array $usernames, callable $onComplete): void;

    /**
     * Adds single username in monitoring book
     *
     * @param string   $userName
     * @param callable $onComplete function(bool)
     */
    public function addUser(string $userName, callable $onComplete): void;

    /**
     * Removes multiple usernames from monitoring book
     *
     * @param string[] $numbers
     * @param string[] $userNames
     * @param callable $onComplete function()
     */
    public function delNumbersAndUsers(array $numbers, array $userNames, callable $onComplete): void;

    /**
     * Removes all contacts from monitoring book
     *
     * @param callable $onComplete function()
     *
     * @return void
     */
    public function cleanContactsBook(callable $onComplete): void;
}
