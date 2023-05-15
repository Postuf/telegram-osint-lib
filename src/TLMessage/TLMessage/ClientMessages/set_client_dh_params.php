<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/schema/mtproto
 */
class set_client_dh_params implements TLClientMessage
{
    public const CONSTRUCTOR = 4110704415;

    private string $oldClientNonce;
    private string $serverNonce;
    private string $encryptedData;

    /**
     * set_client_dh_params constructor.
     *
     * @param string $oldClientNonce
     * @param string $serverNonce
     * @param string $encryptedData
     */
    public function __construct(string $oldClientNonce, string $serverNonce, string $encryptedData)
    {
        $this->oldClientNonce = $oldClientNonce;
        $this->serverNonce = $serverNonce;
        $this->encryptedData = $encryptedData;
    }

    public function getName(): string
    {
        return 'set_client_dh_params';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packBytes($this->oldClientNonce).
            Packer::packBytes($this->serverNonce).
            Packer::packString($this->encryptedData);
    }
}
