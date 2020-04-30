<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage;

interface MessageWithUserId
{
    public function getUserId(): int;
}
