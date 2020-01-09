<?php

declare(strict_types=1);

namespace Scenario;

use Client\InfoObtainingClient\Models\UserInfoModel;
use Closure;
use Exception\TGException;
use Logger\Logger;
use MTSerialization\AnonymousMessage;
use TLMessage\TLMessage\ClientMessages\Api\get_all_chats;
use TLMessage\TLMessage\ClientMessages\Api\get_full_chat;

/**
 * Listing group members
 *
 * This example requires info user (second authkey)
 * to be member of one or several groups, otherwise it is useless.
 *
 * @see get_all_chats
 * @see get_full_chat
 */
class GroupMembersClient extends MyTgClientDebug implements ScenarioInterface
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @throws TGException
     */
    public function startActions()
    {
        $this->infoLogin();
        Logger::log(__CLASS__, 'getting all chats');
        /** @var array $ids */
        $ids = [];
        $this->infoClient->getAllChats(function (AnonymousMessage $message) use (&$ids) {
            /** @see https://core.telegram.org/constructor/messages.chats */
            $chats = $message->getNodes('chats');
            $chatCount = count($chats);
            Logger::log(__CLASS__, "got $chatCount chats");
            foreach ($chats as $chatNode) {
                $id = (int) $chatNode->getValue('id');
                if ($chatNode->getType() != 'chat') {
                    Logger::log(__CLASS__, 'Skipped node of type '.$chatNode->getType());
                    continue;
                }
                $ids[] = $id;

                $this->infoClient->getChatMembers($id, $this->makeChatMemberHandler($id));
            }
        });

        $this->pollAndTerminate();
    }

    /**
     * @param int $id
     *
     * @return Closure
     */
    private function makeChatMemberHandler(int $id): Closure {
        return function (AnonymousMessage $message) use ($id) {
            /** @see https://core.telegram.org/constructor/messages.chatFull */
            $users = $message->getNodes('users');
            foreach ($users as $user) {
                $userId = (int) $user->getValue('id');
                $phone = (string) $user->getValue('phone');
                $phoneSuffix = $phone ? " with phone $phone" : '';
                Logger::log(__CLASS__, "chat $id contains user $userId$phoneSuffix");

                if ($phone) {
                    $this->infoClient->getInfoByPhone(
                        $phone,
                        false,
                        false,
                        Closure::fromCallable([$this, 'userInfoHandler'])
                    );
                }
            }
        };
    }

    private function userInfoHandler(?UserInfoModel $model = null): void {
        if (!$model) {
            return;
        }

        if ($model->username) {
            Logger::log(__CLASS__, "{$model->phone} has username: {$model->username}");
        }

        if ($model->bio) {
            Logger::log(__CLASS__, "{$model->phone} has bio: {$model->bio}");
        }
    }
}
