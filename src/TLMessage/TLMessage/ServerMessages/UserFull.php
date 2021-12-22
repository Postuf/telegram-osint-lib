<?php

declare(strict_types=1);

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
    public static function isIt(AnonymousMessage $tlMessage): bool
    {
        return self::checkType($tlMessage, 'users.userFull');
    }

    /**
     * @throws TGException
     *
     * @return ContactUser
     */
    public function getUser(): ContactUser
    {
        $user = $this->getTlMessage()->getNodes('users')[0];

        return new ContactUser($user);
    }

    public function getAbout(): ?string
    {
        return $this->getTlMessage()->getNode('full_user')->getValue('about');
    }

    public function getCommonChatsCount(): int
    {
        return $this->getTlMessage()->getNode('full_user')->getValue('common_chats_count');
    }
}
