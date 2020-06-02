<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages\Update;

use TelegramOSINT\Client\InfoObtainingClient\Models\ChannelModel;
use TelegramOSINT\Exception\TGException;
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
    public static function isIt(AnonymousMessage $tlMessage): bool
    {
        return self::checkType($tlMessage, 'updates');
    }

    /**
     * @throws TGException
     *
     * @return UpdateUserName[]
     */
    public function getNameUpdates(): array
    {
        $updates = $this->getTlMessage()->getNodes('updates');
        $updateObjects = [];
        foreach ($updates as $update) {
            if (UpdateUserName::isIt($update)) {
                $updateObjects[] = new UpdateUserName($update);
            }
        }

        return $updateObjects;
    }

    /**
     * @throws TGException
     *
     * @return UpdateUserPhone[]
     */
    public function getPhoneUpdates(): array
    {
        $updates = $this->getTlMessage()->getNodes('updates');
        $updateObjects = [];
        foreach ($updates as $update) {
            if (UpdateUserPhone::isIt($update)) {
                $updateObjects[] = new UpdateUserPhone($update);
            }
        }

        return $updateObjects;
    }

    /**
     * @throws TGException
     *
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
