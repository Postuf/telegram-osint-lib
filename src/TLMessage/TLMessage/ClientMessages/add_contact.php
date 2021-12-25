<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\Registration\NameGenerator\NameResource;
use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/contacts.addContact
 */
class add_contact implements TLClientMessage
{
    private const CONSTRUCTOR = 3908330448;

    private int $userId;
    private int $accessHash;

    /**
     * @param int $userId
     * @param int $accessHash
     */
    public function __construct(int $userId, int $accessHash)
    {
        $this->userId = $userId;
        $this->accessHash = $accessHash;
    }

    public function getName(): string
    {
        return 'add_contact';
    }

    public function toBinary(): string
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
