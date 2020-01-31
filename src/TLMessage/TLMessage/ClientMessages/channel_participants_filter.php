<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

class channel_participants_filter implements TLClientMessage
{
    public const PARTICIPANTS_RECENT = -566281095; // 0xde3f3c79
    public const PARTICIPANTS_SEARCH = 106343499; // 0x0656ac4b

    /** @var int */
    private $constructor;
    /** @var int|null */
    private $query;

    public function __construct(int $constructor = self::PARTICIPANTS_RECENT, ?string $query = null)
    {
        $this->constructor = $constructor;
        $this->query = $query;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'channel_participants_filter';
    }

    /**
     * @return string
     */
    public function toBinary()
    {
        $prefix = Packer::packConstructor($this->constructor);

        return $this->constructor == self::PARTICIPANTS_RECENT
            ? $prefix
            : $prefix.Packer::packString($this->query);
    }
}
