<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\LibConfig;
use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/auth.sendCode
 */
class send_sms_code implements TLClientMessage
{
    private const CONSTRUCTOR = 2792825935; // 0xA677244F

    private string $phone;

    /**
     * @param string $phone
     */
    public function __construct(string $phone)
    {
        $this->phone = $phone;
    }

    public function getName(): string
    {
        return 'send_code';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packString($this->phone).
            Packer::packInt(LibConfig::APP_API_ID).
            Packer::packString(LibConfig::APP_API_HASH).
            Packer::packBytes((new send_sms_code_settings())->toBinary());
    }
}
