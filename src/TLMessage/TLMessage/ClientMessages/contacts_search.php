<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/contacts.search
 */
class contacts_search implements TLClientMessage
{
    private const CONSTRUCTOR = 301470424; // 0x11F812D8

    private const DEFAULT_APP_LIMIT = 50;

    private string $nick;
    private int $limit;

    public function __construct(string $nickName, int $limit = self::DEFAULT_APP_LIMIT)
    {
        $this->nick = $nickName;
        $this->limit = $limit;
    }

    public function getName(): string
    {
        return 'contacts_search';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packString($this->nick).
            Packer::packInt($this->limit);
    }
}
