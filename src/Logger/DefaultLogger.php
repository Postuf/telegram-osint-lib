<?php

declare(strict_types=1);

namespace TelegramOSINT\Logger;

class DefaultLogger implements ClientDebugLogger
{
    public function debugLibLog(string $dbgLabel, string $dbgMessage): void
    {
        echo date('d.m.Y H:i:s').' | '.$dbgLabel.': '.$dbgMessage.PHP_EOL;
    }
}
