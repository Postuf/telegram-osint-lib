<?php

declare(strict_types=1);

namespace TelegramOSINT\Client;

interface ContactKeepingClient extends Client
{
    public function addNumbers(array $numbers, callable $onComplete): void;

    /**
     * Adds single username in monitoring book
     *
     * @param string   $userName
     * @param callable $onComplete function(bool)
     */
    public function addUser(string $userName, callable $onComplete): void;

    /**
     * @param array    $numbers
     * @param string[] $users
     * @param callable $onComplete
     */
    public function delNumbersAndUsers(array $numbers, array $users, callable $onComplete): void;

    public function reloadContacts(array $numbers, array $usernames, callable $onComplete): void;

    public function cleanContactsBook(callable $onComplete): void;

    public function getContactByPhone(string $number, callable $onComplete): void;
}
