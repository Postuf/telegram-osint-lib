<?php

declare(strict_types=1);

namespace TelegramOSINT\Scenario;

use TelegramOSINT\Client\InfoObtainingClient\Models\MessageModel;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\Logger\Logger;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\Tools\Proxy;

class GroupMessagesScenario extends AbstractGroupScenario
{
    /** @var callable|null */
    private $handler;

    /** @var string|null */
    private $username;
    /** @var int|null */
    private $userId;
    /** @var int|null */
    private $startTimestamp;

    /**
     * @param callable|null                 $handler        function(MessageModel $message)
     * @param int|null                      $startTimestamp
     * @param string|null                   $username
     * @param Proxy|null                    $proxy
     * @param ClientGeneratorInterface|null $generator
     *
     * @throws TGException
     */
    public function __construct(
        callable $handler = null,
        ?int $startTimestamp = null,
        ?string $username = null,
        ?Proxy $proxy = null,
        ?ClientGeneratorInterface $generator = null
    ) {
        parent::__construct($proxy, $generator);
        $this->handler = $handler;
        $this->startTimestamp = $startTimestamp;
        $this->username = $username;
    }

    /**
     * @return callable function(AnonymousMessage $message)
     */
    public function getAllChatsHandler(): callable
    {
        return function (AnonymousMessage $message) {
            /** @see https://core.telegram.org/constructor/messages.chats */
            $chats = $message->getNodes('chats');
            $chatCount = count($chats);
            Logger::log(__CLASS__, "got $chatCount chats");
            $limit = 100;
            foreach ($chats as $chatNode) {
                $id = (int) $chatNode->getValue('id');
                $accessHash = (int) $chatNode->getValue('access_hash');
                if ($chatNode->getType() != 'chat') {
                    Logger::log(__CLASS__, 'Skipped node of type '.$chatNode->getType());
                    continue;
                }

                usleep(10000);
                $this->parseMessages($id, $accessHash, $limit);
            }
        };

    }

    /**
     * @param callable $cb function()
     *
     * @return callable function(AnonymousMessage $msg)
     */
    private function getUserResolveHandler(callable $cb): callable
    {
        return function (AnonymousMessage $message) use ($cb) {
            if ($message->getType() === 'contacts.resolvedPeer' && $message->getValue('users')) {
                $user = $message->getValue('users')[0];
                if ($user['_'] == 'user') {
                    $this->userId = (int) $user['id'];
                    Logger::log(__CLASS__, "resolved user {$this->username} to {$this->userId}");
                }
            }
            $cb();
        };
    }

    private function getGroupResolveHandler(): callable
    {
        return function (AnonymousMessage $message) {
            $chats = $message->getValue('chats');
            $limit = 100;
            foreach ($chats as $chat) {
                $id = (int) $chat['id'];
                $accessHash = (int) $chat['access_hash'];
                Logger::log(__CLASS__, "getting channel messages for channel $id");
                /** @var array $chat */
                $this->parseMessages($id, $accessHash, $limit);
            }
        };
    }

    public function startActions(): void
    {
        $this->infoLogin();
        usleep(10000);
        Logger::log(__CLASS__, 'getting all chats');
        if ($this->deepLink) {
            Logger::log(__CLASS__, "getting chat by deeplink {$this->deepLink}");
            $parts = explode('/', $this->deepLink);
            $username = $parts[count($parts) - 1];
            $this->infoClient->resolveUsername($username, $this->getResolveHandler(function (AnonymousMessage $message) {
                if ($this->username) {
                    $this->infoClient->resolveUsername($this->username, $this->getUserResolveHandler(function () use ($message) {
                        $handler = $this->getGroupResolveHandler();
                        $handler($message);
                    }));
                } else {
                    $handler = $this->getGroupResolveHandler();
                    $handler($message);
                }
            }));
        } else {
            $this->infoClient->getAllChats($this->getAllChatsHandler());
        }

        $this->pollAndTerminate();
    }

    private function parseMessages(int $id, int $accessHash, int $limit): void
    {
        $this->infoClient->getChannelMessages(
            $id,
            $accessHash,
            $limit,
            null,
            null,
            $this->makeMessagesHandler($id, $accessHash, $limit)
        );
    }

    /**
     * @param int $id
     * @param int $accessHash
     * @param int $limit
     *
     * @return callable function(AnonymousMessage $message)
     */
    private function makeMessagesHandler(int $id, int $accessHash, int $limit): callable
    {
        return function (AnonymousMessage $anonymousMessage) use ($id, $accessHash, $limit) {
            if ($anonymousMessage->getType() != 'messages.channelMessages') {
                Logger::log(__CLASS__, "incorrect message type {$anonymousMessage->getType()}");

                return;
            }

            $messages = $anonymousMessage->getValue('messages');
            /** @var int|null $lastId */
            $lastId = null;
            foreach ($messages as $message) {
                $lastId = (int) $message['id'];
                if ($message['_'] !== 'message') {
                    continue;
                }
                if (!$message['message']) {
                    continue;
                }
                if ($this->userId && $message['from_id'] != $this->userId) {
                    continue;
                }
                if ($this->startTimestamp && $message['date'] < $this->startTimestamp) {
                    return;
                }
                Logger::log(__CLASS__, "got message '{$message['message']}' from {$message['from_id']} at {$message['date']}");
                if ($this->handler) {
                    $handler = $this->handler;
                    $msgModel = new MessageModel(
                        (int) $message['id'],
                        $message['message'],
                        (int) $message['from_id'],
                        (int) $message['date']
                    );
                    $handler($msgModel);
                }

            }

            if ($messages && $lastId !== 1) {
                Logger::log(__CLASS__, "loading more messages, starting with $lastId");
                $this->infoClient->getChannelMessages(
                    $id,
                    $accessHash,
                    $limit,
                    null,
                    $lastId,
                    $this->makeMessagesHandler($id, $accessHash, $limit)
                );
            }
        };
    }
}
