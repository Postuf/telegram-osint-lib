<?php

namespace TLMessage\TLMessage\ServerMessages\Update;

use MTSerialization\AnonymousMessage;
use TLMessage\TLMessage\ServerMessages\Contact\ContactUser;
use TLMessage\TLMessage\TLServerMessage;

class Updates extends TLServerMessage
{
    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    public static function isIt(AnonymousMessage $tlMessage)
    {
        return self::checkType($tlMessage, 'updates');
    }

    /**
     * @return ContactUser[]
     */
    public function getUsers(): array
    {
        $users = $this->getTlMessage()->getNodes('users');
        $userObjects = [];
        foreach ($users as $user)
            $userObjects[] = new ContactUser($user);

        return $userObjects;
    }
}
