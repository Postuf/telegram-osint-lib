<?php

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/account.updateStatus
 */
class update_status implements TLClientMessage
{
    const CONSTRUCTOR = 1713919532; // 0x6628562C

    /**
     * @var bool
     */
    private $online;

    /**
     * update_status constructor.
     *
     * @param bool $online
     */
    public function __construct(bool $online)
    {
        $this->online = $online;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'update_status';
    }

    /**
     * @return string
     */
    public function toBinary()
    {
        $offline = !$this->online;

        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packBool($offline);
    }
}
