<?php

namespace TLMessage\TLMessage\ServerMessages\Contact;


use MTSerialization\AnonymousMessage;
use TLMessage\TLMessage\TLServerMessage;


class ImportedContacts extends TLServerMessage
{

    /**
     * @param AnonymousMessage $tlMessage
     * @return boolean
     */
    public static function isIt(AnonymousMessage $tlMessage)
    {
        return self::checkType($tlMessage, 'contacts.importedContacts');
    }


    /**
     * @return ContactUser[]
     */
    public function getImportedUsers()
    {
        $users = $this->getTlMessage()->getNodes('users');
        $userObjects = [];
        foreach ($users as $user)
            $userObjects[] = new ContactUser($user);

        return $userObjects;
    }


    /**
     * @return ContactClient[]
     */
    public function getImportedClients()
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
    public function getRetryContacts()
    {
        return $this->getTlMessage()->getValue('retry_contacts');
    }

}