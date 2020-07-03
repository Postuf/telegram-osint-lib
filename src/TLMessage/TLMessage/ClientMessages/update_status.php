<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/account.updateStatus
 */
class update_status implements TLClientMessage
{
    public const CONSTRUCTOR = 1713919532; // 0x6628562C

    /**
     * @var bool
     */
    private $online;

    /**
     * @param bool $online
     */
    public function __construct(bool $online)
    {
        $this->online = $online;
    }

    public function getName(): string
    {
        return 'update_status';
    }

    public function toBinary(): string
    {
        $offline = !$this->online;

        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packBool($offline);
    }
}
