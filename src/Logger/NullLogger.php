<?php

declare(strict_types=1);

namespace TelegramOSINT\Logger;

class NullLogger implements ClientDebugLogger
{
    public function debugLibLog(string $dbgLabel, string $dbgMessage)
    {
    }
}
