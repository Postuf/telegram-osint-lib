<?php

namespace TLMessage\TLMessage\ClientMessages\Shared;

use TLMessage\TLMessage\Packer;
use TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/schema/mtproto
 */
class set_client_dh_params implements TLClientMessage
{

    const CONSTRUCTOR = 0xF5045F1F;

    /**
     * @var string
     */
    private $oldClientNonce;
    /**
     * @var string
     */
    private $serverNonce;
    /**
     * @var string
     */
    private $encryptedData;


    /**
     * set_client_dh_params constructor.
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

    /**
     * @return string
     */
    public function getName()
    {
        return 'set_client_dh_params';
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
            Packer::packString($this->encryptedData);
    }

}