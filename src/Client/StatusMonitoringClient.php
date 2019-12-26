<?php

namespace Client;

interface StatusMonitoringClient extends Client
{
    /**
     * Adds single number in monitoring book
     *
     * @param string[] $numbers
     * @param callable $onComplete
     */
    public function addNumbers(array $numbers, callable $onComplete);

    /**
     * Removes single number from monitoring book
     *
     * @param string[] $numbers
     * @param callable $onComplete
     */
    public function delNumbers(array $numbers, callable $onComplete);

    /**
     * Leaves only $numbers in monitoring book, removes any other contacts
     *
     * @param string[] $numbers
     * @param callable $onComplete
     */
    public function reloadNumbers(array $numbers, callable $onComplete);

    /**
     * Adds single username in monitoring book
     *
     * @param string   $userName
     * @param callable $onComplete
     */
    public function addUser(string $userName, callable $onComplete);

    /**
     * Removes single username from monitoring book
     *
     * @param string   $userName
     * @param callable $onComplete
     */
    public function delUser(string $userName, callable $onComplete);

    /**
     * Removes all contacts from monitoring book
     *
     * @param callable $onComplete
     *
     * @return void
     */
    public function cleanMonitoringBook(callable $onComplete);
}
