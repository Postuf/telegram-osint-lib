<?php

namespace TLMessage\TLMessage\ClientMessages\Shared;

use TLMessage\TLMessage\Packer;
use TLMessage\TLMessage\TLClientMessage;

class msgs_ack implements TLClientMessage
{
    const CONSTRUCTOR = 0x62d6b459;

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

    /**
     * @return string
     */
    public function getName()
    {
        return 'msgs_ack';
    }

    /**
     * @return string
     */
    public function toBinary()
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packVector($this->messageIds, $this->getElementGenerator());
    }

    /**
     * @return callable
     */
    private function getElementGenerator()
    {
        return function ($messageId) {
            return Packer::packLong($messageId);
        };
    }
}
