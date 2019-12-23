<?php

namespace Logger;


class DefaultLogger implements ClientDebugLogger
{

    public function debugLibLog(string $dbgLabel, string $dbgMessage)
    {
        echo date("d.m.Y H:i:s") . " | " . $dbgLabel . ": " . $dbgMessage ."\n";
    }

}