<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages;

interface PhotoInterface
{
    public function getBigPhoto(): FileLocation;

    public function getSmallPhoto(): FileLocation;

    public function getDcId(): int;
}
