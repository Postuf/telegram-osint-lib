<?php

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages;

use TelegramOSINT\Exception\TGException;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Contact\ContactUser;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

class UserFull extends TLServerMessage
{
    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    public static function isIt(AnonymousMessage $tlMessage)
    {
        return self::checkType($tlMessage, 'userFull');
    }

    /**
     * @throws TGException
     *
     * @return ContactUser
     */
    public function getUser()
    {
        $user = $this->getTlMessage()->getNode('user');

        return new ContactUser($user);
    }

    /**
     * @return string
     */
    public function getAbout()
    {
        return $this->getTlMessage()->getValue('about');
    }
}
