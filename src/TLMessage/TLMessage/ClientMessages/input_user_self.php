<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;

/** @see https://core.telegram.org/constructor/inputUserSelf */
class input_user_self extends input_user_common
{
    public const CONSTRUCTOR = 4156666175;

    public function getName(): string
    {
        return 'input_user_self';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR);
    }
}
