<?php

declare(strict_types=1);

namespace TelegramOSINT\Scenario;

use TelegramOSINT\Client\InfoObtainingClient\InfoClient;
use TelegramOSINT\Client\InfoObtainingClient\StickerClient;

class StickerClientGenerator extends ClientGenerator
{
    public function getInfoClient(): InfoClient
    {
        return new StickerClient(new BasicClientGenerator($this->proxy, $this->logger));
    }
}
