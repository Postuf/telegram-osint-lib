<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/auth.signIn
 */
class sign_in implements TLClientMessage
{
    public const CONSTRUCTOR = 2371004753; // 0x8d52a951

    private string $phone;
    private string $phoneHash;
    private string $smsCode;

    /**
     * sign_in constructor.
     *
     * @param string $phone
     * @param string $phoneHash
     * @param string $smsCode
     */
    public function __construct(string $phone, string $phoneHash, string $smsCode)
    {
        $this->phone = $phone;
        $this->phoneHash = $phoneHash;
        $this->smsCode = $smsCode;
    }

    public function getName(): string
    {
        return 'sign_in';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packInt(0b1). // phone_code = true, email_verification = false
            Packer::packString($this->phone).
            Packer::packString($this->phoneHash).
            Packer::packString($this->smsCode);
    }
}
