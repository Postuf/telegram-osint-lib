<?php

declare(strict_types=1);

namespace TelegramOSINT\Client;

use TelegramOSINT\Client\InfoObtainingClient\Models\GroupId;

interface ChannelClient
{
    /**
     * @param callable $onComplete function(AnonymousMessage $msg)
     */
    public function getAllChats(callable $onComplete): void;

    public function joinChannel(GroupId $id, callable $onComplete);

    public function getChatMembers(int $id, callable $onComplete): void;

    public function getChannelMembers(GroupId $id, callable $onComplete): void;

    public function getFullChannel(GroupId $id, callable $onComplete): void;

    public function getChatMessages(int $id, int $limit, ?int $since, ?int $lastId, callable $onComplete): void;

    public function getChannelMessages(GroupId $id, int $limit, ?int $since, ?int $lastId, callable $onComplete): void;

    public function getChannelLinks(GroupId $id, int $limit, ?int $since, ?int $lastId, callable $onComplete): void;

    public function getCommonChats(GroupId $id, int $limit, int $max_id, callable $onComplete): void;

    public function getParticipants(GroupId $id, int $offset, callable $onComplete): void;

    public function getParticipantsSearch(GroupId $id, string $username, callable $onComplete): void;
}
