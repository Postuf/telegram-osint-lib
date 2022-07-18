<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/users.getFullUser
 */
class get_full_user implements TLClientMessage
{
    public const CONSTRUCTOR = 3054459160;
    private input_user_common $inputUser;

    public function __construct(input_user_common $inputUser)
    {
        $this->inputUser = $inputUser;
    }

    public function getName(): string
    {
        return 'get_full_user';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            $this->inputUser->toBinary();
    }
}
