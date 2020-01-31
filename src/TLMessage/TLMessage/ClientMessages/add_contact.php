<?php

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\Registration\NameGenerator\NameResource;
use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/contacts.addContact
 */
class add_contact implements TLClientMessage
{
    const CONSTRUCTOR = -386636848; // 0xE8F463D0

    /**
     * @var int
     */
    private $userId;
    /**
     * @var int
     */
    private $accessHash;

    /**
     * @param int $userId
     * @param int $accessHash
     */
    public function __construct(int $userId, int $accessHash)
    {
        $this->userId = $userId;
        $this->accessHash = $accessHash;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'add_contact';
    }

    /**
     * @return string
     */
    public function toBinary()
    {
        $human = new NameResource();
        $contactFirstName = $human->getName();
        $contactLastName = $human->getLastName();

        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packInt(0b0).
            Packer::packBytes((new input_user($this->userId, $this->accessHash))->toBinary()).
            Packer::packString($contactFirstName).
            Packer::packString($contactLastName).
            Packer::packString('');
    }
}
