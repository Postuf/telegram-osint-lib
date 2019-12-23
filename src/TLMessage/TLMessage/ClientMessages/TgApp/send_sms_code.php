<?php

namespace TLMessage\TLMessage\ClientMessages\TgApp;

use LibConfig;
use TLMessage\TLMessage\Packer;
use TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/auth.sendCode
 */
class send_sms_code implements TLClientMessage
{

    const CONSTRUCTOR = -1502141361; // 0xA677244F


    /**
     * @var string
     */
    private $phone;


    /**
     * @var $phone string
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
            Packer::packInt(LibConfig::APP_API_ID).
            Packer::packString(LibConfig::APP_API_HASH).
            Packer::packBytes((new send_sms_code_settings())->toBinary());
    }

}