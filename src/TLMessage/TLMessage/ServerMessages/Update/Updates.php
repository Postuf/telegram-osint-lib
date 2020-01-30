<?php

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages\Update;

use TelegramOSINT\Client\InfoObtainingClient\Models\ChannelModel;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Contact\ContactUser;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

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

    /**
     * @return ChannelModel[]
     */
    public function getChats(): array
    {
        $nodes = $this->getTlMessage()->getNodes('chats');
        $chats = [];
        foreach ($nodes as $chat) {
            $title = $chat->getValue('title');
            $chatModel = new ChannelModel(
                $chat->getValue('id'),
                $chat->getValue('access_hash'),
                $title
            );
            $chats[] = $chatModel;
        }

        return $chats;
    }
}
