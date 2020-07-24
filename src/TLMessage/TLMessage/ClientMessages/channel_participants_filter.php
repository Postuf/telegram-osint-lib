<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

class channel_participants_filter implements TLClientMessage
{
    public const PARTICIPANTS_RECENT = -566281095; // 0xde3f3c79
    public const PARTICIPANTS_SEARCH = 106343499; // 0x0656ac4b
    public const PARTICIPANTS_ADMINS = -1268741783; // 0xb4608969

    /** @var int */
    private int $constructor;
    /** @var int|null */
    private $query;

    public function __construct(int $constructor = self::PARTICIPANTS_RECENT, ?string $query = null)
    {
        $this->constructor = $constructor;
        $this->query = $query;
    }

    public function getName(): string
    {
        return 'channel_participants_filter';
    }

    public function toBinary(): string
    {
        $prefix = Packer::packConstructor($this->constructor);

        return $this->constructor === self::PARTICIPANTS_RECENT
            ? $prefix
            : $prefix.Packer::packString($this->query);
    }
}
