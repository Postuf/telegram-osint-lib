<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages;

interface PhotoInterface
{
    public function getPhotoId(): int;
    public function getDcId(): int;
}
