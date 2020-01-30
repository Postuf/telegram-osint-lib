<?php

declare(strict_types=1);

namespace TelegramOSINT\Client\InfoObtainingClient\Models;

use TelegramOSINT\Scenario\Models\GroupId;

class ChannelModel
{
    public function __construct(int $id, int $accessHash, string $title)
    {
        $this->id = $id;
        $this->accessHash = $accessHash;
        $this->title = $title;
    }

    /** @var int */
    public $id;
    /** @var int */
    public $accessHash;
    /** @var string */
    public $title;

    public function getGroupId(): GroupId
    {
        return new GroupId($this->id, $this->accessHash);
    }
}
