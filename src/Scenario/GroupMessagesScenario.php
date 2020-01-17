<?php

declare(strict_types=1);

namespace TelegramOSINT\Scenario;

use TelegramOSINT\Client\AuthKey\AuthKeyCreator;
use TelegramOSINT\Client\InfoObtainingClient\Models\MessageModel;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\Logger\Logger;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\Scenario\Models\GroupId;
use TelegramOSINT\Scenario\Models\OptionalDateRange;

class GroupMessagesScenario extends InfoClientScenario
{
    /** @var callable|null */
    private $handler;

    /** @var string|null */
    private $username;
    /** @var int|null */
    private $userId;
    /** @var int|null */
    private $startTimestamp;
    /** @var int|null */
    private $endTimestamp;
    /** @var GroupId */
    private $groupIdObj;
    /** @var ClientGeneratorInterface */
    private $generator;

    /**
     * @param GroupId                  $groupId
     * @param ClientGeneratorInterface $generator
     * @param OptionalDateRange        $dateRange
     * @param callable|null            $handler   function(MessageModel $message)
     * @param string|null              $username
     */
    public function __construct(
        GroupId $groupId,
        ClientGeneratorInterface $generator,
        OptionalDateRange $dateRange,
        callable $handler = null,
        ?string $username = null
    ) {
        parent::__construct($generator);
        $this->handler = $handler;
        $this->startTimestamp = $dateRange->getSince();
        $this->endTimestamp = $dateRange->getTo();
        $this->username = $username;
        $this->groupIdObj = $groupId;
        $this->generator = $generator;
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

    /**
     * Connect to telegram with info (second) account
     *
     * @throws TGException
     */
    protected function infoLogin(): void
    {
        $authKey = AuthKeyCreator::createFromString($this->generator->getAuthKeyInfo());
        if (!$this->infoClient->isLoggedIn()) {
            $this->infoClient->login($authKey);
        }
    }

    /**
     * @param bool $pollAndTerminate
     *
     * @throws TGException
     */
    public function startActions(bool $pollAndTerminate = true): void
    {
        $this->infoLogin();
        usleep(10000);
        $limit = 100;
        if ($this->username) {
            $this->infoClient->resolveUsername($this->username, $this->getUserResolveHandler(function () use ($limit) {
                $this->parseMessages($this->groupIdObj->getGroupId(), $this->groupIdObj->getAccessHash(), $limit);
            }));
        } else {
            $this->parseMessages($this->groupIdObj->getGroupId(), $this->groupIdObj->getAccessHash(), $limit);
        }

        if ($pollAndTerminate) {
            $this->pollAndTerminate();
        }
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
                if ($this->endTimestamp && $message['date'] > $this->endTimestamp) {
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
