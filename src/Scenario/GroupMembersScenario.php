<?php

declare(strict_types=1);

namespace TelegramOSINT\Scenario;

use Closure;
use TelegramOSINT\Client\InfoObtainingClient\Models\GroupId;
use TelegramOSINT\Client\InfoObtainingClient\Models\UserInfoModel;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\Logger\Logger;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\get_all_chats;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\get_full_chat;

/**
 * Listing group members
 *
 * This example requires info user to be member of one or several groups, otherwise it is useless.
 *
 * Please note that for public groups you need to be group admin to see member list.
 *
 * @see get_all_chats
 * @see get_full_chat
 */
class GroupMembersScenario extends AbstractGroupScenario
{
    private const PAGE_LIMIT = 100;

    /** @var callable|null function() */
    private $handler;
    /** @var GroupId|null */
    private ?GroupId $groupIdObj;
    /** @var int */
    private int $limit;
    /** @var string|null */
    private ?string $username;

    /**
     * @param GroupId|null                  $groupId
     * @param callable                      $handler   function()
     * @param ClientGeneratorInterface|null $generator
     * @param int                           $limit
     * @param string|null                   $username
     *
     * @throws TGException
     */
    public function __construct(
        ?GroupId $groupId = null,
        callable $handler = null,
        ?ClientGeneratorInterface $generator = null,
        int $limit = 100,
        ?string $username = null
    ) {
        parent::__construct($generator);
        $this->handler = $handler;
        $this->groupIdObj = $groupId;
        $this->limit = $limit;
        $this->username = $username;
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
                if ($chatNode->getType() !== 'chat') {
                    Logger::log(__CLASS__, 'Skipped node of type '.$chatNode->getType());
                    continue;
                }

                usleep(10000);
                $this->infoClient->getChatMembers($id, $this->makeChatMemberHandler($id));
            }
        };
    }

    /**
     * @param bool $pollAndTerminate
     *
     * @throws TGException
     */
    public function startActions(bool $pollAndTerminate = true): void
    {
        $actions = function (): void {
            if ($this->deepLink) {
                Logger::log(__CLASS__, "getting chat by deeplink {$this->deepLink}");

                $parts = explode('/', $this->deepLink);
                $groupName = $parts[count($parts) - 1];
                $onChannelFound = function (AnonymousMessage $message) {
                    foreach ($message->getValue('chats') as $chat) {
                        $id = (int) $chat['id'];
                        Logger::log(__CLASS__, "getting channel members for channel $id");
                        /** @var array $chat */
                        $groupId = new GroupId($id, $chat['access_hash']);
                        if ($this->username) {
                            Logger::log(__CLASS__, "searching chat $id participants for {$this->username}");
                            $this->infoClient->getParticipantsSearch(
                                $groupId,
                                $this->username,
                                $this->makeChatMemberHandler(
                                    $id,
                                    0,
                                    true
                                )
                            );
                        } else {
                            $this->infoClient->getChannelMembers($groupId, $this->makeChatMemberHandler($id));
                        }
                    }
                };

                $this->infoClient->resolveUsername($groupName, $this->getResolveHandler($onChannelFound));
            } elseif ($this->groupIdObj) {
                if ($this->username) {
                    Logger::log(__CLASS__, "searching chat {$this->groupIdObj->getId()} participants for {$this->username}");
                    $this->infoClient->getParticipantsSearch(
                        $this->groupIdObj,
                        $this->username,
                        $this->makeChatMemberHandler(
                            $this->groupIdObj->getId(),
                            0,
                            true
                        )
                    );
                } else {
                    Logger::log(__CLASS__, "getting chat {$this->groupIdObj->getId()} participants");
                    $this->infoClient->getParticipants(
                        $this->groupIdObj,
                        0,
                        $this->makeChatMemberHandler(
                            $this->groupIdObj->getId(),
                            0,
                            true
                        )
                    );
                }
            } else {
                Logger::log(__CLASS__, 'getting all chats');
                $this->infoClient->getAllChats($this->getAllChatsHandler());
            }
        };

        $this->authAndPerformActions($actions, $pollAndTerminate);
    }

    /**
     * @param int  $id
     * @param int  $offset
     * @param bool $continue
     *
     * @return Closure
     */
    private function makeChatMemberHandler(int $id, int $offset = 0, bool $continue = false): Closure
    {
        return function (AnonymousMessage $message) use ($id, $offset, $continue) {
            /** @see https://core.telegram.org/constructor/messages.chatFull */
            /** @see https://core.telegram.org/constructor/channels.channelParticipants */
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
                    $username = (string) $user->getValue('username');
                    $phoneSuffix = $phone ? " with phone $phone" : '';
                    $usernameSuffix = $username ? " with username $username" : '';
                    Logger::log(__CLASS__, "chat $id contains user $userId$phoneSuffix$usernameSuffix");

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
            if ($users && $continue) {
                $newOffset = $offset + self::PAGE_LIMIT;
                if ($newOffset < $this->limit) {
                    Logger::log(__CLASS__, "getting more participants for {$this->groupIdObj->getId()} starting with $newOffset");
                    $this->infoClient->getParticipants(
                        $this->groupIdObj,
                        $newOffset,
                        $this->makeChatMemberHandler(
                            $this->groupIdObj->getId(),
                            $newOffset,
                            true
                        )
                    );
                }
            }
        };
    }

    private function userInfoHandler(?UserInfoModel $model = null): void
    {
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
