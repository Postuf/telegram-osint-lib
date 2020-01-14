<?php

declare(strict_types=1);

namespace Scenario;

use Client\InfoObtainingClient\Models\UserInfoModel;
use Closure;
use Exception\TGException;
use Logger\Logger;
use MTSerialization\AnonymousMessage;
use SocksProxyAsync\Proxy;
use TLMessage\TLMessage\ClientMessages\Api\get_all_chats;
use TLMessage\TLMessage\ClientMessages\Api\get_full_chat;

/**
 * Listing group members
 *
 * This example requires info user (second authkey)
 * to be member of one or several groups, otherwise it is useless.
 *
 * Please note that for public groups you need to be group admin to see member list.
 *
 * @see get_all_chats
 * @see get_full_chat
 */
class GroupMembersClient extends AbstractGroupClient implements ScenarioInterface
{
    /** @var callable|null */
    private $handler;

    /**
     * @param callable                      $handler   function()
     * @param Proxy|null                    $proxy
     * @param ClientGeneratorInterface|null $generator
     *
     * @throws TGException
     */
    public function __construct(
        callable $handler = null,
        ?Proxy $proxy = null,
        ?ClientGeneratorInterface $generator = null
    ) {
        parent::__construct($proxy, $generator);
        $this->handler = $handler;
    }

    private function getAllChatsHandler(): callable
    {
        return function (AnonymousMessage $message) {
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

                sleep(1);
                $this->infoClient->getChatMembers($id, $this->makeChatMemberHandler($id));
            }
        };
    }

    /**
     * @throws TGException
     */
    public function startActions(): void
    {
        $this->infoLogin();
        sleep(1);
        Logger::log(__CLASS__, 'getting all chats');
        if ($this->deepLink) {
            Logger::log(__CLASS__, "getting chat by deeplink {$this->deepLink}");
            $parts = explode('/', $this->deepLink);
            $username = $parts[count($parts) - 1];
            $this->infoClient->resolveUsername($username, $this->getResolveHandler(function (AnonymousMessage $message) {
                $chats = $message->getValue('chats');
                foreach ($chats as $chat) {
                    $id = (int) $chat['id'];
                    Logger::log(__CLASS__, "getting channel members for channel $id");
                    /** @var array $chat */
                    $this->infoClient->getChannelMembers($id, $chat['access_hash'], $this->makeChatMemberHandler($id));
                }
            }));
        } else {
            $this->infoClient->getAllChats($this->getAllChatsHandler());
        }

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
            if ($this->handler) {
                $models = [];
                foreach ($users as $user) {
                    $model = new UserInfoModel();
                    $model->id = (int) $user->getValue('id');
                    $model->phone = (string) $user->getValue('phone');
                    $model->username = (string) $user->getValue('username');
                    $models[] = $model;
                }
                $handler = $this->handler;
                $handler($models);
            } else {
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
