<?php

namespace TelegramOSINT\Logger;

class DefaultLogger implements ClientDebugLogger
{
    public function debugLibLog(string $dbgLabel, string $dbgMessage): void
    {
        echo date('d.m.Y H:i:s').' | '.$dbgLabel.': '.$dbgMessage."\n";
    }
}
