<?php

declare(strict_types=1);

namespace Helpers\TraceConverter\Traces;

use Helpers\TraceConverter\Contracts\TraceInterface;
use JsonSerializable;

class Trace implements TraceInterface, JsonSerializable
{
    /**
     * Field name used in JSON serialization.
     */
    const JSON_FIELD_TIMESTAMP = 'trace-timestamp';

    /**
     * Field name used in JSON serialization.
     */
    const JSON_FIELD_RECORDS = 'records';

    /**
     * @var float
     */
    private $timestamp;

    /**
     * @var array
     */
    private $records;

    /**
     * @param float $timestamp
     * @param array $records
     */
    public function __construct(float $timestamp, array $records)
    {
        $this->timestamp = $timestamp;
        $this->records = $records;
    }

    /**
     * {@inheritdoc}
     */
    public function getTimeStamp(): float
    {
        return $this->timestamp;
    }

    /**
     * {@inheritdoc}
     */
    public function getRecords(): array
    {
        return $this->records;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [
            self::JSON_FIELD_TIMESTAMP => $this->getTimeStamp(),
            self::JSON_FIELD_RECORDS   => $this->getRecords(),
        ];
    }
}
