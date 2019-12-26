<?php

namespace TLMessage\TLMessage\ServerMessages\Contact;

use MTSerialization\AnonymousMessage;
use TLMessage\TLMessage\TLServerMessage;

class ContactFound extends TLServerMessage
{
    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    public static function isIt(AnonymousMessage $tlMessage)
    {
        return self::checkType($tlMessage, 'contacts.found');
    }

    /**
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
