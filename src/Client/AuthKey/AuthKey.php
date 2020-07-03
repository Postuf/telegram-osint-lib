<?php

declare(strict_types=1);

namespace TelegramOSINT\Client\AuthKey;

use TelegramOSINT\TGConnection\DataCentre;

interface AuthKey
{
    public function getRawAuthKey(): string;

    public function getSerializedAuthKey(): string;

    public function getAttachedDC(): DataCentre;
}
