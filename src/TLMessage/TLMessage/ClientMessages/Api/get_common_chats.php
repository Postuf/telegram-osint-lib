<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages\Api;

use TelegramOSINT\TLMessage\TLMessage\ClientMessages\Shared\input_user;
use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/messages.getCommonChats
 */
class get_common_chats implements TLClientMessage
{
    const CONSTRUCTOR = 218777796; // 0xd0a48c4

    /** @var int */
    private $user_id;
    /** @var int */
    private $max_id;
    /** @var int */
    private $limit;
    /** @var int */
    private $accessHash;

    public function __construct(int $user_id, int $limit, int $max_id = 0, int $accessHash = 0)
    {
        $this->user_id = $user_id;
        $this->limit = $limit;
        $this->max_id = $max_id;
        $this->accessHash = $accessHash;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'get_common_chats';
    }

    /**
     * {@inheritdoc}
     */
    public function toBinary()
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            (new input_user($this->user_id, $this->accessHash))->toBinary().
            Packer::packInt($this->max_id).
            Packer::packInt($this->limit);
    }
    
}