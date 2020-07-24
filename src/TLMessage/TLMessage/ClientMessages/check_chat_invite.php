<?php

/** @noinspection PhpUnused
 * @noinspection UnknownInspectionInspection
 */

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/messages.checkChatInvite
 */
class check_chat_invite implements TLClientMessage
{
    private const CONSTRUCTOR = 1051570619; // 0x3eadb1bb

    /** @var string */
    private string $hash;

    public function __construct(string $hash)
    {
        $this->hash = $hash;
    }

    public function getName(): string
    {
        return 'check_chat_invite';
    }

    public function toBinary(): string
    {
        return Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packString($this->hash);
    }
}
