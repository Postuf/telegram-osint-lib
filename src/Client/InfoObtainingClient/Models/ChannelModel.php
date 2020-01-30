<?php

declare(strict_types=1);

namespace TelegramOSINT\Client\InfoObtainingClient\Models;

use TelegramOSINT\Scenario\Models\GroupId;

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
    public $id;
    /** @var int */
    public $accessHash;
    /** @var string */
    public $title;
    /** @var string|null */
    public $username;

    public function getGroupId(): GroupId
    {
        return new GroupId($this->id, $this->accessHash);
    }
}
