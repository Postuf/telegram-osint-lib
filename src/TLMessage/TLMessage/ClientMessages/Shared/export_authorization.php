<?php

/** @noinspection SpellCheckingInspection */

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages\Shared;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/auth.exportAuthorization
 */
class export_authorization implements TLClientMessage
{
    const CONSTRUCTOR = -440401971; // 0xE5BFFFCD

    /**
     * @var int
     */
    private $foreignDcId;

    /**
     * export_authorization constructor.
     *
     * @param int $foreignDdIc
     */
    public function __construct(int $foreignDdIc)
    {
        $this->foreignDcId = $foreignDdIc;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'export_authorization';
    }

    /**
     * @return string
     */
    public function toBinary()
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packInt($this->foreignDcId);
    }
}
