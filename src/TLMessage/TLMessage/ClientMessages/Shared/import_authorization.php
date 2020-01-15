<?php

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages\Shared;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/auth.importAuthorization
 */
class import_authorization implements TLClientMessage
{
    const CONSTRUCTOR = -470837741; // 0xE3EF9613

    /**
     * @var int
     */
    private $userId;
    /**
     * @var string
     */
    private $keyBytes;

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

    /**
     * @return string
     */
    public function getName()
    {
        return 'import_authorization';
    }

    /**
     * @return string
     */
    public function toBinary()
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packInt($this->userId).
            Packer::packString($this->keyBytes);
    }
}
