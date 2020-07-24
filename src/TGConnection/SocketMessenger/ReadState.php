<?php

declare(strict_types=1);

namespace TelegramOSINT\TGConnection\SocketMessenger;

class ReadState
{
    /**
     * @var float
     */
    private $timeStart;
    /** @var int */
    private int $length = 0;
    /** @var string */
    private string $read = '';
    /** @var string */
    private string $lengthValue = '';
    /** @var int */
    private int $readLength = 0;

    public function __construct()
    {
        $this->timeStart = microtime(true);
    }

    public function setLength(int $length): void
    {
        $this->length = $length;
    }

    public function addRead(string $read): void
    {
        $this->read .= $read;
        $this->readLength = strlen($this->read);
    }

    public function ready(): bool
    {
        return $this->length && $this->readLength === $this->length;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function getCurrentLength(): int
    {
        return $this->readLength;
    }

    public function getPayload(): string
    {
        return $this->read;
    }

    public function setLengthValue(string $value): void
    {
        $this->lengthValue = $value;
    }

    public function getLengthValue(): string
    {
        return $this->lengthValue;
    }

    public function getTimeStart(): float
    {
        return $this->timeStart;
    }
}
