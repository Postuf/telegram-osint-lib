<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages;

use TelegramOSINT\Client\InfoObtainingClient\Models\ChannelModel;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

class Chats extends TLServerMessage
{
    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    public static function isIt(AnonymousMessage $tlMessage)
    {
        return self::checkType($tlMessage, 'messages.chats');
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
                $title,
                $chat->getValue('username')
            );
            $chats[] = $chatModel;
        }

        return $chats;
    }
}
