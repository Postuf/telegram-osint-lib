<?php

namespace TLMessage\TLMessage\ServerMessages\Contact;


use Exception\TGException;
use MTSerialization\AnonymousMessage;
use TLMessage\TLMessage\TLServerMessage;


class CurrentContacts extends TLServerMessage
{

    /**
     * @param AnonymousMessage $tlMessage
     * @return boolean
     */
    public static function isIt(AnonymousMessage $tlMessage)
    {
        return self::checkType($tlMessage, 'contacts.contacts');
    }


    /**
     * @return ContactUser[]
     *
     * @throws TGException
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