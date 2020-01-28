<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages\TgApp;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

class channel_participants_filter implements TLClientMessage
{
    public const PARTICIPANTS_RECENT = -566281095; // 0xde3f3c79

    private $constructor;

    public function __construct(int $constructor = self::PARTICIPANTS_RECENT)
    {
        $this->constructor = $constructor;
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
        return Packer::packConstructor($this->constructor);
    }
}
