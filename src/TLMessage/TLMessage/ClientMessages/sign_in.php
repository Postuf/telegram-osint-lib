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
    public const CONSTRUCTOR = 3168081281; // 0xbcd51581

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
            Packer::packString($this->phone).
            Packer::packString($this->phoneHash).
            Packer::packString($this->smsCode);
    }
}
