<?php

namespace TelegramOSINT\Client\StatusWatcherClient\Models;

class ImportResult
{
    /** @var string[] */
    public array $importedPhones = [];
    /** @var string[] */
    public array $replacedPhones = [];
    /** @var int[] */
    public array $retryContacts = [];
}
