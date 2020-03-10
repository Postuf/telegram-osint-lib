<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages;

use TelegramOSINT\Exception\TGException;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Contact\ContactUser;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

class AuthorizationContactUser extends TLServerMessage
{
    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    public static function isIt(AnonymousMessage $tlMessage): bool
    {
        return self::checkType($tlMessage, 'auth.authorization');
    }

    /**
     * @throws TGException
     *
     * @return ContactUser
     */
    public function getUser(): ContactUser
    {
        $contact = $this->getTlMessage()->getNode('user');

        return new ContactUser($contact);
    }
}
