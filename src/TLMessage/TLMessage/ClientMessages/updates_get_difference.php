<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/updates.getDifference
 */
class updates_get_difference implements TLClientMessage
{
    public const CONSTRUCTOR = 630429265; // 0x25939651

    private $qts;
    private $pts;
    private $date;

    /**
     * @param int $pts
     * @param int $qts
     * @param int $date
     */
    public function __construct(int $pts, int $qts, int $date)
    {
        $this->pts = $pts;
        $this->qts = $qts;
        $this->date = $date;
    }

    public function getName(): string
    {
        return 'updates_get_difference';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packInt(0b0).
            Packer::packInt($this->pts).
            Packer::packInt($this->date).
            Packer::packInt($this->qts);
    }
}
