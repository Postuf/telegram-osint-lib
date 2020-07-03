<?php

declare(strict_types=1);

namespace TelegramOSINT\TGConnection\SocketMessenger;

use TelegramOSINT\TLMessage\TLMessage\ServerMessages\DcOption;

abstract class BaseSocketMessenger implements SocketMessenger
{
    public function isDcAppropriate(DcOption $dc): bool
    {
        return (bool) preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/', $dc->getIp());
    }
}
