<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages\Contact;

use TelegramOSINT\Client\InfoObtainingClient\Models\ChannelModel;
use TelegramOSINT\Client\InfoObtainingClient\Models\UserInfoModel;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Peer\Peer;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Peer\PeerChannel;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Peer\PeerChat;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Peer\PeerUser;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

/**
 * @see https://core.telegram.org/constructor/contacts.resolvedPeer
 */
class ResolvedPeer extends TLServerMessage
{
    /**
     * {@inheritdoc}
     */
    public static function isIt(AnonymousMessage $tlMessage): bool
    {
        return $tlMessage->getType() === 'contacts.resolvedPeer';
    }

    /**
     * @throws TGException
     *
     * @return Peer
     */
    public function getPeer(): Peer
    {
        $peer = $this->getTlMessage()->getNode('peer');
        switch ($peer->getType()) {
            case 'peerUser':
                return new PeerUser($peer->getValue('user_id'));
            case 'peerChat':
                return new PeerChat($peer->getValue('chat_id'));
            case 'peerChannel':
                return new PeerChannel($peer->getValue('channel_id'));
        }

        throw new TGException(0, 'Invalid peer type: '.$this->getTlMessage()->getNode('peer')->getType());
    }

    /**
     * @return UserInfoModel[]
     */
    public function getUsers(): array
    {
        $users = [];
        foreach ($this->getTlMessage()->getNodes('users') as $userNode) {
            $id = (int) $userNode->getValue('id');
            $user = new UserInfoModel();
            $user->id = $id;
            $user->username = $userNode->getValue('username');
            $users[] = $user;
        }

        return $users;
    }

    /**
     * @return ChannelModel[]
     */
    public function getChats(): array
    {
        $chats = [];
        foreach ($this->getTlMessage()->getNodes('chats') as $chatNode) {
            $id = (int) $chatNode->getValue('id');
            $accessHash = (int) $chatNode->getValue('access_hash');
            $chats[] = new ChannelModel($id, $accessHash, $chatNode->getValue('title'));
        }

        return $chats;
    }
}
