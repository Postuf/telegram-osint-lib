<?php

declare(strict_types=1);

namespace TelegramOSINT\Scenario;

use TelegramOSINT\Client\AuthKey\AuthKeyCreator;
use TelegramOSINT\Client\InfoObtainingClient\InfoClient;
use TelegramOSINT\Client\InfoObtainingClient\Models\UserInfoModel;
use TelegramOSINT\Logger\Logger;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\Scenario\Models\GroupRequest;
use TelegramOSINT\Tools\Proxy;

class CommonChatsScenario extends InfoClientScenario

{
    /** @var callable|null function(int $groupId, string $title) */
    private $handler;
    /** @var string[] */
    private $groupnames;
    /** @var string[] */
    private $commonChats = [];
    /** @var string */
    private $phone;
    /** @var array */
    private $resolvedGroups = [];

    /**
     * CommonChatsScenario constructor.
     * @param ClientGeneratorInterface $clientGenerator
     * @param array $groupnames
     * @param string $phone
     * @param callable|null $handler
     * @throws \TelegramOSINT\Exception\TGException
     */
    public function __construct(
        ClientGeneratorInterface $clientGenerator,
        array $groupnames,
        string $phone,
        ?callable $handler = null
    ) {
        parent::__construct($clientGenerator);
        $this->handler = $handler;
        $this->groupnames = $groupnames;
        $this->phone = $phone;
    }

    public function startActions(bool $pollAndTerminate = true): void
    {
        $this->login();
        usleep(10000);
        $this->subscribeToChats(function(){
            $this->getCommonChats(function() {
                Logger::log("DEBUG", "Common chats: " . print_r($this->commonChats, true));
            });
        });

        if ($pollAndTerminate) {
            $this->pollAndTerminate();
        }
    }

    public function subscribeToChats(callable $onComplete)
    {
        $callback = function() use ($onComplete) {
            Logger::log("DEBUG", "Run user resolver...");
            $joinCnt = count($this->resolvedGroups);
            foreach ($this->resolvedGroups as $group) {
                $this->joinGroup($group['id'], $group['accessHash'], function(AnonymousMessage $message) use ($group, &$joinCnt, $onComplete) {
                    $joinCnt--;
                    Logger::log(__CLASS__, 'subscribe to channel '. $group['title']);
                    if ($joinCnt == 0) {
                        $onComplete();
                    }
                });
            }
        };
        $groupsCnt = count($this->groupnames);
        foreach ($this->groupnames as $groupName) {
            $this->infoClient->resolveUsername($groupName, function(AnonymousMessage $message) use ($groupName, &$groupsCnt, $callback) {
                $groupsCnt--;
                if ($message->getType() === 'contacts.resolvedPeer' && ($chats = $message->getValue('chats'))){
                    foreach ($chats as $chat) {
                        $id = (int) $chat['id'];
                        $accessHash = (int) $chat['access_hash'];
                        $this->resolvedGroups[] = [
                            'title' => $groupName,
                            'id' => $id,
                            'accessHash' => $accessHash,
                        ];
                        break;
                    }
                }
                if ($groupsCnt == 0) {
                    $callback();
                }
            });
        }
    }

    /**
     * @param string $phone
     * @param array $groups
     * @param callable|null $callback
     * @throws \TelegramOSINT\Exception\TGException
     */
    public function getCommonChats(?callable $callback = null)
    {
        $client = new UserContactsScenario([$this->phone], function(UserInfoModel $user) use ($callback) {
            $this->infoClient->getCommonChats($user->id, $user->accessHash, 100, 0, function(AnonymousMessage $message) use($callback) {
                Logger::log(__CLASS__, "get common chats");
                $chats = $message->getNodes('chats');

                foreach ($chats as $chatNode) {
                    if ($chatNode->getType() != 'chat' && $chatNode->getType() != 'channel') {
                        Logger::log(__CLASS__, "Skipped node of type " . $chatNode->getType());
                        continue;
                    }

                    $this->commonChats[] = $chatNode->getValue('username');
                }

                if ($callback) {
                    $callback();
                }
            });
        });
        $client->startActions();
    }

    private function joinGroup(int $groupId, ?int $accessHash, ?callable $callback)
    {
        $this->infoClient->joinChannel($groupId, $accessHash, $callback);
    }

}
