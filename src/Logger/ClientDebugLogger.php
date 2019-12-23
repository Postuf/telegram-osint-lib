<?php

namespace Logger;


interface ClientDebugLogger
{

    public function debugLibLog(string $dbgLabel, string $dbgMessage);

}