<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

class msgs_ack implements TLClientMessage
{
    private const CONSTRUCTOR = 0x62d6b459;

    /**
     * @param array $msgIds
     */
    private $messageIds;

    /**
     * @param int[] $msgIds
     */
    public function __construct($msgIds)
    {
        $this->messageIds = $msgIds;
    }

    public function getName(): string
    {
        return 'msgs_ack';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packVector($this->messageIds, $this->getElementGenerator());
    }

    /**
     * @return callable
     */
    private function getElementGenerator(): callable
    {
        return static function ($messageId) {
            return Packer::packLong($messageId);
        };
    }
}
