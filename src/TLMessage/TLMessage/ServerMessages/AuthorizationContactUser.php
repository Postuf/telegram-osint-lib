<?php

namespace TLMessage\TLMessage\ServerMessages;

use MTSerialization\AnonymousMessage;
use TLMessage\TLMessage\ServerMessages\Contact\ContactUser;
use TLMessage\TLMessage\TLServerMessage;

class AuthorizationContactUser extends TLServerMessage
{
    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    public static function isIt(AnonymousMessage $tlMessage)
    {
        return self::checkType($tlMessage, 'auth.authorization');
    }

    /**
     * @return ContactUser
     */
    public function getUser()
    {
        $contact = $this->getTlMessage()->getNode('user');

        return new ContactUser($contact);
    }
}
