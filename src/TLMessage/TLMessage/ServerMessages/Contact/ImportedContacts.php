<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages\Contact;

use TelegramOSINT\Exception\TGException;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

class ImportedContacts extends TLServerMessage
{
    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    public static function isIt(AnonymousMessage $tlMessage): bool
    {
        return self::checkType($tlMessage, 'contacts.importedContacts');
    }

    /**
     * @throws TGException
     *
     * @return ContactUser[]
     */
    public function getImportedUsers(): array
    {
        $users = $this->getTlMessage()->getNodes('users');
        $userObjects = [];
        foreach ($users as $user)
            $userObjects[] = new ContactUser($user);

        return $userObjects;
    }

    /**
     * @throws TGException
     *
     * @return ContactClient[]
     */
    public function getImportedClients(): array
    {
        $clients = $this->getTlMessage()->getNodes('imported');
        $clientObjects = [];
        foreach ($clients as $client)
            $clientObjects[] = new ContactClient($client);

        return $clientObjects;
    }

    /**
     * @return array (index => client_id)
     */
    public function getRetryContacts(): array
    {
        return $this->getTlMessage()->getValue('retry_contacts');
    }
}
