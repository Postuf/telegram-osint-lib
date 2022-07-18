<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;

/** @see https://core.telegram.org/constructor/inputChannel */
class input_channel extends input_peer
{
    public const CONSTRUCTOR = 4082822184;

    private int $channelId;
    /** @var int */
    private int $accessHash;

    /**
     * @param int $channelId
     * @param int $accessHash
     */
    public function __construct(int $channelId, int $accessHash)
    {
        $this->channelId = $channelId;
        $this->accessHash = $accessHash;
    }

    public function getName(): string
    {
        return 'input_channel';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packLong($this->channelId).
            Packer::packLong($this->accessHash);
    }
}
