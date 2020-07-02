<?php

declare(strict_types=1);

namespace TelegramOSINT\MTSerialization\OwnImplementation;

use TelegramOSINT\Exception\TGException;

class ByteStream
{
    /** @var string */
    private $stream;
    /** @var int */
    private $pointer = 0;
    /** @var bool */
    private $len;

    /**
     * @param string $binaryData
     */
    public function __construct($binaryData)
    {
        $this->stream = $binaryData;
        $this->len = strlen($this->stream);
    }

    /**
     * @param int $length
     *
     * @throws TGException
     *
     * @return false|string
     */
    public function read($length): string
    {
        $data = substr($this->stream, $this->pointer, $length);

        if($this->pointer + $length > $this->len) {
            throw new TGException(TGException::ERR_DESERIALIZER_BROKEN_BINARY_READ);
        }
        $this->pointer += $length;

        return $data;
    }

    public function readToEnd(): string
    {
        if (!$this->stream) {
            return '';
        }

        $data = substr($this->stream, $this->pointer);
        $this->pointer = strlen($this->stream);

        return $data;
    }

    public function isEmpty(): bool
    {
        return $this->pointer === $this->len;
    }

    public function __toString()
    {
        return (string) substr($this->stream, $this->pointer);
    }
}
