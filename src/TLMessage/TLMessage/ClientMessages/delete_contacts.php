<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/contacts.deleteContacts
 */
class delete_contacts implements TLClientMessage
{
    private const CONSTRUCTOR = 157945344; // 0x96A0E00
    /** @see https://core.telegram.org/type/InputUser */
    private const INPUT_USER_CONSTRUCTOR = -668391402; // 0xD8292816

    /**
     * @param array
     */
    private $contactsToDelete = [];

    /**
     * @param int $accessHash
     * @param int $userId
     */
    public function addToDelete(int $accessHash, int $userId): void
    {
        $this->contactsToDelete[] = [
            'access_hash' => $accessHash,
            'user_id'     => $userId,
        ];
    }

    public function getContactsToDelete(): array
    {
        return $this->contactsToDelete;
    }

    public function getName(): string
    {
        return 'delete_contacts';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packVector($this->contactsToDelete, $this->getElementGenerator());
    }

    private function getElementGenerator(): callable
    {
        return static function ($userData) {
            return
                Packer::packConstructor(self::INPUT_USER_CONSTRUCTOR).
                Packer::packInt($userData['user_id']).
                Packer::packLong($userData['access_hash']);
        };
    }
}
