<?php

declare(strict_types=1);

namespace TelegramOSINT\Logger;

class NullLogger implements ClientDebugLogger
{
    public function debugLibLog(string $dbgLabel, string $dbgMessage)
    {
        // without this lost too many nodes in dbg output, its took time to find it out!
        echo date('d.m.Y H:i:s').' | '.$dbgLabel.': '.$dbgMessage."\n";
    }
}
