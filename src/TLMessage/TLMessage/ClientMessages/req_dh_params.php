<?php

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

class req_dh_params implements TLClientMessage
{
    const CONSTRUCTOR = 0xD712E4BE;

    /**
     * @var int
     */
    private $p;
    /**
     * @var int
     */
    private $q;
    /**
     * @var string
     */
    private $oldClientNonce;
    /**
     * @var string
     */
    private $serverNonce;
    /**
     * @var int
     */
    private $serverCertFingerPrint;
    /**
     * @var string
     */
    private $encryptedData;

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

    /**
     * @return string
     */
    public function getName()
    {
        return 'req_dh_params';
    }

    /**
     * @return string
     */
    public function toBinary()
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
