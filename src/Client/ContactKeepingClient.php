<?php

declare(strict_types=1);

namespace TelegramOSINT\Client;

interface ContactKeepingClient
{
    public function addNumbers(array $numbers, callable $onComplete): void;

    public function delNumbers(array $numbers, callable $onComplete): void;

    public function reloadContacts(array $numbers, array $usernames, callable $onComplete): void;

    public function cleanContactsBook(callable $onComplete): void;
}
