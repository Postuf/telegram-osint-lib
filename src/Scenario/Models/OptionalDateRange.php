<?php

declare(strict_types=1);

namespace TelegramOSINT\Scenario\Models;

class OptionalDateRange
{
    /** @var int|null */
    private $since;
    /** @var int|null */
    private $to;

    public function __construct(?int $since = null, ?int $to = null)
    {
        $this->since = $since;
        $this->to = $to;
    }

    public function getSince(): ?int
    {
        return $this->since;
    }

    public function getTo(): ?int
    {
        return $this->to;
    }
}
