<?php

declare(strict_types=1);

namespace Helpers\TraceConverter\Contracts;

interface TraceInterface
{
    /**
     * Get start timestamp for the trace.
     *
     * @return float
     */
    public function getTimeStamp(): float;

    /**
     * Get trace records.
     *
     * @return array|TraceRecordInterface[]
     */
    public function getRecords(): array;
}
