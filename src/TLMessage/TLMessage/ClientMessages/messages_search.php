<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/messages.search
 */
class messages_search implements TLClientMessage
{
    private const CONSTRUCTOR = 2700978018;

    /** @var int */
    private int $chatId;
    /** var int */
    private int $limit;
    /** var int */
    private int $accessHash;
    /** @var int */
    private int $since;
    /** @var int */
    private int $lastId;

    public function __construct(int $chatId, int $limit = 100, int $accessHash = 0, int $since = 0, int $lastId = 0)
    {
        $this->chatId = $chatId;
        $this->limit = $limit;
        $this->since = $since;
        $this->lastId = $lastId;
        $this->accessHash = $accessHash;
    }

    public function getName(): string
    {
        return 'search';
    }

    public function toBinary(): string
    {
        $flags = 0x0; // 0b00000001; 0x3

        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packInt($flags).
            (!$this->accessHash
                ? new input_peer_chat($this->chatId)
                : new input_peer_channel($this->chatId, $this->accessHash))
                    ->toBinary().
            Packer::packString(''). //q
            (new input_messages_filter_url())->toBinary(). // filter
            Packer::packInt($this->since). //min_date
            Packer::packInt(0). //max_date
            Packer::packInt($this->lastId). //offset_id
            Packer::packInt(0). //and_offset
            Packer::packInt($this->limit). //limit
            Packer::packInt(0). //max_id
            Packer::packInt(0). //min_id
            Packer::packLong(0); //hash
    }
}
