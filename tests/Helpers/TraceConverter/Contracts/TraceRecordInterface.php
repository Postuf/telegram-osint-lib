<?php

declare(strict_types=1);

namespace Helpers\TraceConverter\Contracts;

use TelegramOSINT\MTSerialization\AnonymousMessage;

interface TraceRecordInterface
{
    /**
     * Get trace record type (e.g. `messages.chats`, `upload.file`, etc)
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Get actual message (possibly with other messages included) that was sent.
     *
     * @return AnonymousMessage
     */
    public function getMessage(): AnonymousMessage;

    /**
     * Get timestamp when the message was sent.
     *
     * @return float
     */
    public function getTimeStamp(): float;
}
