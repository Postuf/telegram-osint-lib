<?php

declare(strict_types=1);

namespace TelegramOSINT\Client\BasicClient;

use JsonException;
use TelegramOSINT\MTSerialization\AnonymousMessage;

class TracingBasicClientImpl extends BasicClientImpl
{
    /** @var float */
    private $traceStart;
    /** @var array */
    private array $traceLog = [];

    public function __construct()
    {
        parent::__construct();
        $this->traceStart = microtime(true);
    }

    public function __destruct()
    {
        parent::__destruct();

        if ($this->traceLog) {
            try {
                $encoded = json_encode([$this->traceStart, $this->traceLog], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
                file_put_contents(md5((string) mt_rand()).'.txt', $encoded);
            } catch (JsonException $e) {
            }
        }
    }

    protected function prePollMessage(): ?AnonymousMessage
    {
        $readMessage = parent::prePollMessage();
        if ($readMessage) {
            $this->recordTrace($readMessage);
        }

        return $readMessage;
    }

    private function recordTrace(AnonymousMessage $message): void
    {
        $this->traceLog[] = [$message->getType(), bin2hex(serialize($message)), microtime(true)];
    }
}
