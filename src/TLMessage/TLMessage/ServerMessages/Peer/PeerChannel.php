<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages\Peer;

class PeerChannel implements Peer
{
    /** @var int */
    private int $channelId;

    public function __construct(int $channelId)
    {
        $this->channelId = $channelId;
    }

    public function getId(): int
    {
        return $this->channelId;
    }
}
