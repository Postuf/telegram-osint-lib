<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages\Peer;

class PeerUser implements Peer
{
    /** @var int */
    private $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function getId(): int
    {
        return $this->userId;
    }
}
