<?php

namespace TLMessage\TLMessage\ClientMessages\Api;

use LibConfig;
use TLMessage\TLMessage\Packer;
use TLMessage\TLMessage\TLClientMessage;

class send_sms_code implements TLClientMessage
{

    const CONSTRUCTOR = 0xd16ff372;


    /**
     * @var string
     */
    private $phone;


    /**
     * @param string $phone
     */
    public function __construct(string $phone)
    {
        $this->phone = $phone;
    }


    /**
     * @return string
     */
    public function getName()
    {
        return 'send_code';
    }


    /**
     * @return string
     */
    public function toBinary()
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packString($this->phone).
            Packer::packInt(0).                         //0-code, 1-link, 5-code-telegram
            Packer::packInt(LibConfig::OWN_API_ID).
            Packer::packString(LibConfig::OWN_API_HASH);
    }

}