<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/messages.getFullChannel
 */
class get_full_channel implements TLClientMessage
{
    const CONSTRUCTOR = 141781513; // 0x8736a09

    /** @var int */
    private $channelId;
    /** @var int */
    private $accessHash;

    public function __construct(int $channelId, int $accessHash)
    {
        $this->channelId = $channelId;
        $this->accessHash = $accessHash;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'get_full_chat';
    }

    /**
     * {@inheritdoc}
     */
    public function toBinary()
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            (new input_channel($this->channelId, $this->accessHash))->toBinary();
    }
}
