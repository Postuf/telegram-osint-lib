<?php

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages\Shared;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/contacts.deleteContacts
 */
class delete_contacts implements TLClientMessage
{
    const CONSTRUCTOR = 157945344; // 0x96A0E00
    /** @see https://core.telegram.org/type/InputUser */
    const INPUT_USER_CONSTRUCTOR = -668391402; // 0xD8292816

    /**
     * @param array
     */
    private $contactsToDelete = [];

    /**
     * @param int $accessHash
     * @param int $userId
     */
    public function addToDelete(int $accessHash, int $userId)
    {
        $this->contactsToDelete[] = [
            'access_hash' => $accessHash,
            'user_id'     => $userId,
        ];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'delete_contacts';
    }

    /**
     * @return string
     */
    public function toBinary()
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packVector($this->contactsToDelete, $this->getElementGenerator());
    }

    /**
     * @return callable
     */
    private function getElementGenerator()
    {
        return function ($userData) {
            return
                Packer::packConstructor(self::INPUT_USER_CONSTRUCTOR).
                Packer::packInt($userData['user_id']).
                Packer::packLong($userData['access_hash']);
        };
    }
}
