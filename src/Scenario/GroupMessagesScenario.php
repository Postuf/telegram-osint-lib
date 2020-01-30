<?php

declare(strict_types=1);

namespace TelegramOSINT\Scenario;

use TelegramOSINT\Client\InfoObtainingClient\Models\MessageModel;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\Logger\Logger;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\Scenario\Models\GroupId;
use TelegramOSINT\Scenario\Models\OptionalDateRange;

class GroupMessagesScenario extends InfoClientScenario
{
    private const FIELD_MSG_FROM_ID = 'from_id';

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
    /** @var int */
    private $callLimit;
    /** @var bool */
    private $resolveUsernames;
    /** @var string[] int -> string */
    private $userMap = [];

    /**
     * @param GroupId                  $groupId
     * @param ClientGeneratorInterface $generator
     * @param OptionalDateRange        $dateRange
     * @param callable|null            $handler          function(MessageModel $message)
     * @param string|null              $username
     * @param int|null                 $callLimit
     * @param bool                     $resolveUsernames
     *
     * @throws TGException
     */
    public function __construct(
        GroupId $groupId,
        ClientGeneratorInterface $generator,
        OptionalDateRange $dateRange,
        callable $handler = null,
        ?string $username = null,
        ?int $callLimit = 100,
        bool $resolveUsernames = false
    ) {
        parent::__construct($generator);
        $this->handler = $handler;
        $this->startTimestamp = $dateRange->getSince();
        $this->endTimestamp = $dateRange->getTo();
        $this->username = $username;
        $this->groupIdObj = $groupId;
        $this->generator = $generator;
        $this->callLimit = $callLimit;
        $this->resolveUsernames = $resolveUsernames;
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
     * @param bool $pollAndTerminate
     *
     * @throws TGException
     */
    public function startActions(bool $pollAndTerminate = true): void
    {
        $this->login();
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

    /**
     * @param bool $pollAndTerminate
     *
     * @throws TGException
     */
    public function startLinkParse(bool $pollAndTerminate = true): void
    {
        $this->login();
        usleep(10000);
        $limit = 100;

        if ($this->username) {
            $this->infoClient->resolveUsername($this->username, $this->getUserResolveHandler(function () use ($limit) {
                $this->parseLinks($this->groupIdObj->getGroupId(), $this->groupIdObj->getAccessHash(), $limit);
            }));
        } else {
            $this->parseLinks($this->groupIdObj->getGroupId(), $this->groupIdObj->getAccessHash(), $limit);
        }

        if ($pollAndTerminate) {
            $this->pollAndTerminate();
        }
    }

    private function parseLinks(int $id, int $accessHash, int $limit): void
    {
        $this->infoClient->getChannelLinks($id, $limit, $accessHash, null, null, $this->makeMessagesHandler($id, $accessHash, $limit));
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
            $users = $anonymousMessage->getValue('users');
            foreach ($users as $user) {
                if (!$user['username']) {
                    continue;
                }
                $this->userMap[$user['id']] = $user['username'];
            }

            $messages = $anonymousMessage->getValue('messages');
            /** @var int|null $lastId */
            $lastId = null;
            $bunchSkipped = false;
            $flagCounter = count($messages);
            foreach ($messages as $message) {
                $flagCounter--;
                $lastId = (int) $message['id'];
                if ($message['_'] !== 'message') {
                    continue;
                }
                if (!$message['message']) {
                    continue;
                }
                if ($this->userId && $message[self::FIELD_MSG_FROM_ID] != $this->userId) {
                    continue;
                }
                if ($this->endTimestamp && $message['date'] > $this->endTimestamp) {
                    if (!$bunchSkipped) {
                        Logger::log(__CLASS__, "skipping bunch due to later date ({$message['date']} > {$this->endTimestamp})");
                        $bunchSkipped = true;
                    }
                    continue;
                }

                if ($this->startTimestamp && $message['date'] < $this->startTimestamp) {
                    Logger::log(__CLASS__, 'skipping msg due to earlier date');
                    if ($this->handler) {
                        $handler = $this->handler;
                        $handler(null, null, -1);
                    }

                    return;
                }

                $fnLog = function ($message, $from = null) {
                    $body = $message['message'];
                    $body = str_replace("\n", ' \\\\ ', $body);
                    if (!$from) {
                        $from = $message[self::FIELD_MSG_FROM_ID];
                    }
                    Logger::log(__CLASS__, "got message '{$body}' from $from at ".date('Y-m-d H:i:s', $message['date']));
                };
                if ($this->resolveUsernames) {
                    if (!isset($this->userMap[(int) $message[self::FIELD_MSG_FROM_ID]])) {
                        $fnLog($message);
                    } else {
                        $fnLog($message, $this->userMap[(int) $message[self::FIELD_MSG_FROM_ID]]);
                    }
                } else {
                    $fnLog($message);
                }
                if ($this->handler) {
                    $handler = $this->handler;
                    $msgModel = new MessageModel(
                        (int) $message['id'],
                        $message['message'],
                        (int) $message[self::FIELD_MSG_FROM_ID],
                        (int) $message['date']
                    );
                    $handler($msgModel, $message, $flagCounter);
                }

            }

            if ($messages && $lastId !== 1) {
                $this->callLimit--;
                if (!$this->callLimit) {
                    Logger::log(__CLASS__, 'not loading more messages, max call count reached');

                    return;
                }
                Logger::log(__CLASS__, "loading more messages, starting with $lastId");
                usleep(500000);
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
