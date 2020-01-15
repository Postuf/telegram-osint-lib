<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages\Api;

use TelegramOSINT\TLMessage\TLMessage\ClientMessages\TgApp\input_peer_channel;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\TgApp\input_peer_chat;
use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/messages.getHistory
 */
class get_history implements TLClientMessage
{
    const CONSTRUCTOR = -591691168; // 0xdcbb8260

    /** @var int */
    private $chatId;
    /** @var int */
    private $limit;
    /** @var int */
    private $since;
    /** @var int */
    private $maxId;
    /** @var int */
    private $accessHash = 0;

    public function __construct(int $chatId, int $limit = 1000, int $since = 0, int $maxId = 0, int $accessHash = 0)
    {
        $this->chatId = $chatId;
        $this->limit = $limit;
        $this->since = $since;
        $this->maxId = $maxId;
        $this->accessHash = $accessHash;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'get_history';
    }

    /**
     * {@inheritdoc}
     */
    public function toBinary()
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            (!$this->accessHash
                ? new input_peer_chat($this->chatId)
                : new input_peer_channel($this->chatId, $this->accessHash))
                    ->toBinary().
            Packer::packInt(0). // offset_id
            Packer::packInt($this->since). // offset_date
            Packer::packInt(0). // add_offset
            Packer::packInt($this->limit). // limit
            Packer::packInt($this->maxId). // max_id
            Packer::packInt(0). // min_id
            Packer::packInt(0); // hash
    }
}
