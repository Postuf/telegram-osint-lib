<?php

namespace TLMessage\TLMessage\ClientMessages\TgApp;

use TLMessage\TLMessage\Packer;
use TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/updates.getDifference
 */
class updates_get_difference implements TLClientMessage
{
    const CONSTRUCTOR = 630429265; // 0x25939651

    private $qts;
    private $pts;
    private $date;

    /**
     * updates_get_difference constructor.
     *
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

    /**
     * @return string
     */
    public function getName()
    {
        return 'updates_get_difference';
    }

    /**
     * @return string
     */
    public function toBinary()
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packInt(0b0).
            Packer::packInt($this->pts).
            Packer::packInt($this->date).
            Packer::packInt($this->qts);
    }
}
