<?php

declare(strict_types=1);

namespace TelegramOSINT\Client\InfoObtainingClient\Models;

class GroupId
{
    /** @var int */
    private $groupId;
    /** @var int */
    private $accessHash;

    public function __construct(int $groupId, int $accessHash)
    {
        $this->groupId = $groupId;
        $this->accessHash = $accessHash;
    }

    public function getId(): int
    {
        return $this->groupId;
    }

    public function getAccessHash(): int
    {
        return $this->accessHash;
    }
}
