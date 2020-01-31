<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages\Peer;

class PeerChat implements Peer
{
    /** @var int */
    private $chatId;

    public function __construct(int $chatId)
    {
        $this->chatId = $chatId;
    }

    public function getId(): int
    {
        return $this->chatId;
    }
}
