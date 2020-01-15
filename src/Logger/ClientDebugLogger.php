<?php

namespace TelegramOSINT\Logger;

interface ClientDebugLogger
{
    public function debugLibLog(string $dbgLabel, string $dbgMessage);
}
