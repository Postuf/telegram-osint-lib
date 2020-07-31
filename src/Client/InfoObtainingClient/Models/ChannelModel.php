<?php

declare(strict_types=1);

namespace TelegramOSINT\Client\InfoObtainingClient\Models;

class ChannelModel
{
    public function __construct(int $id, int $accessHash, string $title, ?string $username = null)
    {
        $this->id = $id;
        $this->accessHash = $accessHash;
        $this->title = $title;
        $this->username = $username;
    }

    /** @var int */
    public int $id;
    /** @var int */
    public int $accessHash;
    /** @var string */
    public string $title;
    /** @var string|null */
    public ?string $username;

    public function getGroupId(): GroupId
    {
        return new GroupId($this->id, $this->accessHash);
    }
}
