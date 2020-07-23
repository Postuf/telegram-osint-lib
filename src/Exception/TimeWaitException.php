<?php
declare(strict_types=1);

namespace TelegramOSINT\Exception;

class TimeWaitException extends TGException
{
    /** @var int */
    private $waitTime;

    /**
     * @param int $code
     * @param string $clarification
     * @param int $waitTime
     */
    public function __construct(int $code = 0, $clarification = "", int $waitTime = 0)
    {
        parent::__construct($code, $clarification);
        $this->waitTime = $waitTime;
    }

    public function getWaitTime(): int
    {
        return $this->waitTime;
    }
}