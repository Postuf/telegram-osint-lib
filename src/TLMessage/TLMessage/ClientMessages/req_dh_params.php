<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

class req_dh_params implements TLClientMessage
{
    public const CONSTRUCTOR = 0xD712E4BE;

    private int $p;
    private int $q;
    private string $oldClientNonce;
    private string $serverNonce;
    private int $serverCertFingerPrint;
    private string $encryptedData;

    /**
     * req_dh_params constructor.
     *
     * @param string $oldClientNonce
     * @param string $serverNonce
     * @param int    $p
     * @param int    $q
     * @param int    $serverCertFingerPrint
     * @param string $encryptedData
     */
    public function __construct(string $oldClientNonce, string $serverNonce, int $p, int $q, int $serverCertFingerPrint, string $encryptedData)
    {
        $this->p = $p;
        $this->q = $q;
        $this->oldClientNonce = $oldClientNonce;
        $this->serverNonce = $serverNonce;
        $this->serverCertFingerPrint = $serverCertFingerPrint;
        $this->encryptedData = $encryptedData;
    }

    public function getName(): string
    {
        return 'req_dh_params';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packBytes($this->oldClientNonce).
            Packer::packBytes($this->serverNonce).
            Packer::packIntAsBytesLittleEndian($this->p).
            Packer::packIntAsBytesLittleEndian($this->q).
            Packer::packLong($this->serverCertFingerPrint).
            Packer::packString($this->encryptedData);
    }
}
