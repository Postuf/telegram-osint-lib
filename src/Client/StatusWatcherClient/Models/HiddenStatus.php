<?php

namespace Client\StatusWatcherClient\Models;


class HiddenStatus
{

    const HIDDEN_EMPTY = 'empty';
    const HIDDEN_SEEN_RECENTLY = 'recently';
    const HIDDEN_SEEN_LAST_WEEK = 'last_week';
    const HIDDEN_SEEN_LAST_MONTH = 'last_month';
    const HIDDEN_SEEN_LONG_AGO = 'long_ago';

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