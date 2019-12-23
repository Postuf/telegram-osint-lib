<?php

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__ . '/MyTgClientDebug.php';

use Client\InfoObtainingClient\Models\UserInfoModel;
use Exception\TGException;
use Logger\Logger;
use MTSerialization\AnonymousMessage;

/**
 * Пример выгрузки участников группы
 */
class GroupMembersClient extends MyTgClientDebug {
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
        Logger::log(__CLASS__, "getting all chats");
        /** @var array $ids */
        $ids = [];
        $this->infoClient->getAllChats(function (AnonymousMessage $message) use(&$ids) {
            /** @see https://core.telegram.org/constructor/messages.chats */
            $chats = $message->getNodes('chats');
            $chatCount = count($chats);
            Logger::log(__CLASS__, "got $chatCount chats");
            foreach ($chats as $chatNode) {
                $id = (int) $chatNode->getValue('id');
                $ids[] = $id;

                $this->infoClient->getChatMembers($id, $this->makeChatMemberHandler($id));
            }
        });

        $this->pollAndTerminate();
    }

    /**
     * @param int $id
     * @return Closure
     */
    private function makeChatMemberHandler(int $id) {
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

    private function userInfoHandler(?UserInfoModel $model = null) {
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

/** @noinspection PhpUnhandledExceptionInspection */
(new GroupMembersClient())->startActions();
