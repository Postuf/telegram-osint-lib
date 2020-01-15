<?php

namespace TelegramOSINT\MTSerialization\OwnImplementation;

use TelegramOSINT\Exception\TGException;

class ByteStream
{
    /** @var string */
    private $stream;
    /** @var int */
    private $pointer = 0;

    /**
     * @param string $binaryData
     */
    public function __construct($binaryData)
    {
        $this->stream = $binaryData;
        $this->pointer = 0;
    }

    /**
     * @param int $length
     *
     * @throws TGException
     *
     * @return false|string
     */
    public function read($length)
    {
        $data = substr($this->stream, $this->pointer, $length);

        if(strlen($data) != $length)
            throw new TGException(TGException::ERR_DESERIALIZER_BROKEN_BINARY_READ);
        $this->pointer += $length;

        return $data;
    }

    public function isEmpty(): bool
    {
        return $this->pointer == strlen($this->stream);
    }

    public function __toString()
    {
        return substr($this->stream, $this->pointer);
    }
}
