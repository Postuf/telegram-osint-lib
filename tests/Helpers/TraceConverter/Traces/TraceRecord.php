<?php

declare(strict_types=1);

namespace Helpers\TraceConverter\Traces;

use Helpers\TraceConverter\Contracts\TraceRecordInterface;
use JsonSerializable;
use TelegramOSINT\MTSerialization\AnonymousMessage;

class TraceRecord implements TraceRecordInterface, JsonSerializable
{
    /**
     * Field name used in JSON serialization.
     */
    public const JSON_FIELD_TYPE = 'record-type';

    /**
     * Field name used in JSON serialization.
     */
    public const JSON_FIELD_MESSAGE = 'message';

    /**
     * Field name used in JSON serialization.
     */
    public const JSON_FIELD_TIMESTAMP = 'record-timestamp';

    private string $type;

    private AnonymousMessage $message;

    private float $timestamp;

    /**
     * @param string           $type
     * @param AnonymousMessage $message
     * @param float            $timestamp
     */
    public function __construct(string $type, AnonymousMessage $message, float $timestamp)
    {
        $this->type = $type;
        $this->message = $message;
        $this->timestamp = $timestamp;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage(): AnonymousMessage
    {
        return $this->message;
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
    public function jsonSerialize()
    {
        return [
            self::JSON_FIELD_TYPE      => $this->getType(),
            self::JSON_FIELD_MESSAGE   => $this->getMessage(),
            self::JSON_FIELD_TIMESTAMP => $this->getTimeStamp(),
        ];
    }
}
