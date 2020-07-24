<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/constructor/inputFileLocation
 */
class input_file_location implements TLClientMessage
{
    public const CONSTRUCTOR = -539317279; // 0xDFDAABE1

    private int $volumeId;
    private int $localId;
    private int $secret;
    private string $reference;

    /**
     * @param int    $volumeId
     * @param int    $localId
     * @param int    $secret
     * @param string $reference
     */
    public function __construct($volumeId, $localId, $secret, $reference)
    {
        $this->volumeId = $volumeId;
        $this->localId = $localId;
        $this->secret = $secret;
        $this->reference = $reference;
    }

    public function getName(): string
    {
        return 'input_file_location';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packLong($this->volumeId).
            Packer::packInt($this->localId).
            Packer::packLong($this->secret).
            Packer::packString($this->reference);
    }
}
