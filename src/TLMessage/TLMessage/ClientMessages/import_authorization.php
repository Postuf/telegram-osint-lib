<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/auth.importAuthorization
 */
class import_authorization implements TLClientMessage
{
    public const CONSTRUCTOR = 2776268205;

    private int $userId;
    private string $keyBytes;

    /**
     * import_authorization constructor.
     *
     * @param int    $userId
     * @param string $keyBytes
     */
    public function __construct(int $userId, string $keyBytes)
    {
        $this->userId = $userId;
        $this->keyBytes = $keyBytes;
    }

    public function getName(): string
    {
        return 'import_authorization';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packLong($this->userId).
            Packer::packString($this->keyBytes);
    }
}
