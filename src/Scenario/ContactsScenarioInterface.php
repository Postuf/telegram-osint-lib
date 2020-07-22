<?php

declare(strict_types=1);

namespace TelegramOSINT\Scenario;

interface ContactsScenarioInterface
{
    /**
     * @param string[] $numbers
     * @param bool     $withPhoto
     * @param bool     $largePhoto
     */
    public function parseNumbers(array $numbers, bool $withPhoto = false, bool $largePhoto = false): void;
}
