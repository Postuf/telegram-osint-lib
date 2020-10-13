<?php
declare(strict_types=1);

namespace TelegramOSINT\Exception;

class TimeWaitException extends TGException
{
    private int $waitTimeSeconds;

    /**
     * @param int $code
     * @param string $clarification
     * @param int $waitTime
     */
    public function __construct(int $code = 0, $clarification = "", int $waitTime = 0)
    {
        parent::__construct($code, $clarification . ' _wait_ ' . $waitTime);
        $this->waitTimeSeconds = $waitTime;
    }

    public function getWaitTimeSeconds(): int
    {
        return $this->waitTimeSeconds;
    }
}