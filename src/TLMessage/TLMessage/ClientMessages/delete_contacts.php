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
    private const CONSTRUCTOR = 157945344;

    /**
     * @param array
     */
    private array $contactsToDelete = [];

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
            return (new input_user($userData['user_id'], $userData['access_hash']))->toBinary();
        };
    }
}
