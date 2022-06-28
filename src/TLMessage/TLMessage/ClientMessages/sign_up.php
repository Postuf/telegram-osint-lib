<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/auth.signUp
 */
class sign_up implements TLClientMessage
{
    public const CONSTRUCTOR = 2163139623; // 0x80EEE427

    private string $phone;
    private string $phoneHash;
    private string $firstName;
    private string $lastName;

    /**
     * sign_up constructor.
     *
     * @param string $phone
     * @param string $phoneHash
     * @param string $firstName
     * @param string $lastName
     */
    public function __construct(string $phone, string $phoneHash, string $firstName, string $lastName)
    {
        $this->phone = $phone;
        $this->phoneHash = $phoneHash;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    public function getName(): string
    {
        return 'sign_up';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packString($this->phone).
            Packer::packString($this->phoneHash).
            Packer::packString($this->firstName).
            Packer::packString($this->lastName);
    }
}
