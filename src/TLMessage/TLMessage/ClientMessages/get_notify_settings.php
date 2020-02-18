<?php

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/** @see https://core.telegram.org/method/account.getNotifySettings */
class get_notify_settings implements TLClientMessage
{
    const CONSTRUCTOR = 313765169; // 0x12B3AD31

    /**
     * @var TLClientMessage
     */
    private $peer;

    public function __construct(TLClientMessage $peer)
    {
        $this->peer = $peer;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'get_notify_settings';
    }

    /**
     * @return string
     */
    public function toBinary()
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packBytes($this->peer->toBinary());
    }
}
