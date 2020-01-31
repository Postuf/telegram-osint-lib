<?php

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages\TgApp;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

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
        $apiId = getenv('APP_API_ID');

        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packString($this->phone).
            Packer::packInt($apiId).
            Packer::packString(getenv('APP_API_HASH')).
            Packer::packBytes((new send_sms_code_settings())->toBinary());
    }
}
