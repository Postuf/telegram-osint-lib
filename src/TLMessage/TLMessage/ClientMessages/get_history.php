<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/messages.getHistory
 */
class get_history implements TLClientMessage
{
    private const CONSTRUCTOR = 1143203525;

    /** @var int */
    private int $chatId;
    /** @var int */
    private int $limit;
    /** @var int */
    private int $since;
    /** @var int */
    private int $maxId;
    /** @var int */
    private int $accessHash;

    public function __construct(int $chatId, int $limit = 1000, int $since = 0, int $maxId = 0, int $accessHash = 0)
    {
        $this->chatId = $chatId;
        $this->limit = $limit;
        $this->since = $since;
        $this->maxId = $maxId;
        $this->accessHash = $accessHash;
    }

    public function getName(): string
    {
        return 'get_history';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            (!$this->accessHash
                ? new input_peer_chat($this->chatId)
                : new input_peer_channel($this->chatId, $this->accessHash))
                    ->toBinary().
            Packer::packInt($this->maxId). // offset_id
            Packer::packInt($this->since). // offset_date
            Packer::packInt(0). // add_offset
            Packer::packInt($this->limit). // limit
            Packer::packInt(0). // max_id
            Packer::packInt(0). // min_id
            Packer::packLong(0); // hash
    }
}
