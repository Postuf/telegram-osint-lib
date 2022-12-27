<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/messages.sendMessage
 */
class send_message implements TLClientMessage
{
    private const CONSTRUCTOR = 482476935;

    /** @var input_peer */
    private input_peer $inputPeer;
    /** @var string */
    private string $message;

    public function __construct(input_peer $inputPeer, string $message)
    {
        $this->inputPeer = $inputPeer;
        $this->message = $message;
    }

    public function getName(): string
    {
        return 'send_message';
    }

    public function toBinary(): string
    {
        return Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packInt(0). // flags not implemented
            $this->inputPeer->toBinary().
            Packer::packString($this->message).
            Packer::packLong(mt_rand());
    }
}
