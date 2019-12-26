<?php

namespace TLMessage\TLMessage\ServerMessages\Contact;

use Exception\TGException;
use MTSerialization\AnonymousMessage;
use TLMessage\TLMessage\TLServerMessage;

class CurrentContacts extends TLServerMessage
{
    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    public static function isIt(AnonymousMessage $tlMessage)
    {
        return self::checkType($tlMessage, 'contacts.contacts');
    }

    /**
     * @throws TGException
     *
     * @return ContactUser[]
     */
    public function getUsers()
    {
        $users = $this->getTlMessage()->getNodes('users');
        $userObjects = [];
        foreach ($users as $user)
            $userObjects[] = new ContactUser($user);

        return $userObjects;
    }
}
