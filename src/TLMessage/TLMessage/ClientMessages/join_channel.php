<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/channels.joinChannel
 */
class join_channel implements TLClientMessage
{
    const CONSTRUCTOR = 615851205; //0x24b524c5

    /** @var int */
    private $chatId;
    /** @var int */
    private $accessHash;

    public function __construct(int $chatId, int $accessHash)
    {
        $this->chatId = $chatId;
        $this->accessHash = $accessHash;
    }

    public function getName(): string
    {
        return 'join_channel';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            (new input_channel($this->chatId, $this->accessHash))->toBinary();
    }
}
