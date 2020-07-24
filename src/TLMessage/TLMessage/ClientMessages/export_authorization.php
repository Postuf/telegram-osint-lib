<?php

/** @noinspection SpellCheckingInspection */

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/auth.exportAuthorization
 */
class export_authorization implements TLClientMessage
{
    private const CONSTRUCTOR = -440401971; // 0xE5BFFFCD

    private int $foreignDcId;

    /**
     * export_authorization constructor.
     *
     * @param int $foreignDdIc
     */
    public function __construct(int $foreignDdIc)
    {
        $this->foreignDcId = $foreignDdIc;
    }

    public function getName(): string
    {
        return 'export_authorization';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packInt($this->foreignDcId);
    }
}
