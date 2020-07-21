<?php

declare(strict_types=1);

namespace Helpers;

use TelegramOSINT\Client\InfoObtainingClient\InfoClient;
use TelegramOSINT\Client\InfoObtainingClient\StickerClient;

class StickerTestClientGenerator extends TestClientGenerator
{
    public function getInfoClient(): InfoClient
    {
        if (!$this->client) {
            $this->client = new StickerClient($this->generator);
        }

        return $this->client;
    }
}
