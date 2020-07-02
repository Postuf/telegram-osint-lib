<?php

namespace TelegramOSINT\Client\StatusWatcherClient\Models;

class HiddenStatus
{
    public const HIDDEN_EMPTY = 'empty';
    public const HIDDEN_SEEN_RECENTLY = 'recently';
    public const HIDDEN_SEEN_LAST_WEEK = 'last_week';
    public const HIDDEN_SEEN_LAST_MONTH = 'last_month';
    public const HIDDEN_SEEN_LONG_AGO = 'long_ago';

    /**
     * @var string
     */
    private $statusCode;

    /**
     * @param string $statusCode
     */
    public function __construct(string $statusCode)
    {
        $this->statusCode = $statusCode;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->statusCode;
    }
}
