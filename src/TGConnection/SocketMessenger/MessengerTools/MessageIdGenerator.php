<?php

namespace TelegramOSINT\TGConnection\SocketMessenger\MessengerTools;

class MessageIdGenerator
{
    /**
     * @var int
     */
    private $msgId = 0;

    /**
     * @return int
     */
    public function generateNext(): int
    {
        [$msec, $sec] = explode(' ', microtime());

        $msec *= 10 ** 6; // microseconds to whole number
        $msec <<= 2; // multiply by 4

        $msgId = ($sec << 32) | $msec; // apply bitwise OR to microseconds and sec * 2^32

        // compare with existing id
        if ($msgId <= $this->msgId) { // if new id is less or equal
            $msgId = $this->msgId + 4; // then increase it
        }

        return $msgId;
    }
}
