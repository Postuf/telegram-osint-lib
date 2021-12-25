<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;

/**
 * @see https://core.telegram.org/constructor/inputPeerEmpty
 */
class input_peer_empty extends input_peer
{
    public const CONSTRUCTOR = 2134579434;

    public function getName(): string
    {
        return 'input_peer_empty';
    }

    public function toBinary(): string
    {
        return Packer::packConstructor(self::CONSTRUCTOR);
    }
}
