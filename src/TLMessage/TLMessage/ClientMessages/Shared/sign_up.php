<?php

namespace TLMessage\TLMessage\ClientMessages\Shared;

use TLMessage\TLMessage\Packer;
use TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/auth.signUp
 */
class sign_up implements TLClientMessage
{
    const CONSTRUCTOR = -2131827673; // 0x80EEE427

    /**
     * @var string
     */
    private $phone;
    /**
     * @var string
     */
    private $phoneHash;
    /**
     * @var string
     */
    private $firstName;
    /**
     * @var string
     */
    private $lastName;

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

    /**
     * @return string
     */
    public function getName()
    {
        return 'sign_up';
    }

    /**
     * @return string
     */
    public function toBinary()
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packString($this->phone).
            Packer::packString($this->phoneHash).
            Packer::packString($this->firstName).
            Packer::packString($this->lastName);
    }
}
