<?php

declare(strict_types=1);

namespace TelegramOSINT\Scenario;

use TelegramOSINT\Client\InfoObtainingClient\Models\UserInfoModel;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\Logger\Logger;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Chats;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Contact\ResolvedPeer;
use TelegramOSINT\Tools\CacheMap;

/**
 * Search common chats for user
 */
class CommonChatsScenario extends InfoClientScenario

{
    /** @var callable|null function(int, string) */
    private $handler;
    /** @var string[] */
    private $groupnames = [];
    /** @var array */
    private $groupMap;
    /** @var string[] */
    private $interests;
    /** @var string[] */
    private $commonChats = [];
    /** @var string */
    private $phone;
    /** @var array */
    private $resolvedGroups = [];
    /** @var CacheMap */
    private $resolveCache;

    /**
     * @param ClientGeneratorInterface $clientGenerator
     * @param array                    $groupMap
     * @param string                   $phone
     * @param callable|null            $handler
     *
     * @throws TGException
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function __construct(
        ClientGeneratorInterface $clientGenerator,
        array $groupMap,
        string $phone,
        ?callable $handler = null
    ) {
        parent::__construct($clientGenerator);
        $this->handler = $handler;
        $this->groupMap = $groupMap;
        $this->phone = $phone;
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->resolveCache = new CacheMap(__FILE__.'.tmp');
    }

    /**
     * @param bool $pollAndTerminate
     *
     * @throws TGException
     */
    public function startActions(bool $pollAndTerminate = true): void
    {
        foreach ($this->groupMap as $groupName => $groupTypes) {
            $this->groupnames[] = $groupName;
            foreach ($groupTypes as $groupType) {
                $this->interests[] = $groupType;
            }
        }

        $this->authAndPerformActions(function (): void {
            usleep(10000);

            $this->subscribeToChats(function () {
                Logger::log(__CLASS__, 'subscription complete');
                $this->getCommonChats(function () {
                    Logger::log(__CLASS__, 'Common chats: '.print_r($this->commonChats, true));

                    $this->getInterest();
                });
            });
        }, $pollAndTerminate);
    }

    /**
     * @param callable $onComplete function()
     * @noinspection PhpUnusedParameterInspection
     */
    public function subscribeToChats(callable $onComplete): void
    {
        $callback = function () use ($onComplete) {
            Logger::log('DEBUG', 'Run user resolver...');
            $joinCnt = count($this->resolvedGroups);
            foreach ($this->resolvedGroups as $group) {
                $this->joinGroup($group['id'], $group['accessHash'], static function (AnonymousMessage $message) use ($group, &$joinCnt, $onComplete) {
                    $joinCnt--;
                    Logger::log(__CLASS__, 'subscribe to channel '.$group['title']);
                    if ($joinCnt === 0) {
                        $onComplete();
                    }
                });
                usleep(400000);
            }
        };
        $groupsCnt = count($this->groupnames);
        $completedFlag = false;

        foreach ($this->groupnames as $groupName) {
            if ($info = $this->resolveCache->get($groupName)) {
                Logger::log(__CLASS__, "got $groupName from cache");
                $this->resolvedGroups[] = $info;
                $groupsCnt--;
            } else {
                $this->infoClient->resolveUsername($groupName, function (AnonymousMessage $message) use ($groupName, &$groupsCnt, &$completedFlag, $callback) {
                    $groupsCnt--;
                    if (ResolvedPeer::isIt($message)
                        && ($resolvedPeer = new ResolvedPeer($message))
                        && $resolvedPeer->getChats()) {
                        /** @noinspection LoopWhichDoesNotLoopInspection */
                        foreach ($resolvedPeer->getChats() as $chat) {
                            $id = (int) $chat->id;
                            $accessHash = (int) $chat->accessHash;
                            $value = [
                                'title'      => $groupName,
                                'id'         => $id,
                                'accessHash' => $accessHash,
                            ];
                            $this->resolveCache->set($groupName, $value);
                            $this->resolvedGroups[] = $value;
                            break;
                        }
                    }
                    if ($groupsCnt === 0) {
                        $completedFlag = true;
                        $callback();
                    }
                });
                usleep(710000);
            }

            if ($groupsCnt === 0 && !$completedFlag) {
                $callback();
            }
        }
    }

    /**
     * @param callable|null $callback function()
     *
     * @throws TGException
     */
    public function getCommonChats(?callable $callback = null): void
    {
        $client = new UserContactsScenario([$this->phone], [], function (UserInfoModel $user) use ($callback) {
            $this->infoClient->getCommonChats($user->id, $user->accessHash, 100, 0, function (AnonymousMessage $message) use ($callback) {
                if (!Chats::isIt($message)) {
                    return;
                }
                foreach ((new Chats($message))->getChats() as $chat) {

                    $this->commonChats[] = strtolower($chat->username);
                }

                if ($callback) {
                    $callback();
                }
            });
        }, $this->getGenerator(), false, false);
        $client->startActions(false);
    }

    /**
     * @param int           $groupId
     * @param int|null      $accessHash
     * @param callable|null $callback   function(AnonymousMessage $message)
     */
    private function joinGroup(int $groupId, ?int $accessHash, ?callable $callback): void
    {
        $this->infoClient->joinChannel($groupId, $accessHash, $callback);
    }

    private function getInterest(): void
    {
        $result = [];
        foreach ($this->commonChats as $commonChat) {
            foreach ($this->groupMap[$commonChat] as $interest) {
                $result[$interest] = isset($result[$interest]) ? $result[$interest] + 1 : 1;
            }
        }

        if ($this->handler) {
            $cb = $this->handler;
            $cb($result);
        }
    }
}
