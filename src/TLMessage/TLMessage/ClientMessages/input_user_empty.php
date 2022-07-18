<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;

/** @see https://core.telegram.org/constructor/inputUserEmpty */
class input_user_empty extends input_user_common
{
    public const CONSTRUCTOR = 3112732367;

    public function getName(): string
    {
        return 'input_user_empty';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR);
    }
}
