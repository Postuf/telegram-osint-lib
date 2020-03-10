<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages;

use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

class BadServerSalt extends TLServerMessage
{
    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    public static function isIt(AnonymousMessage $tlMessage): bool
    {
        return self::checkType($tlMessage, 'bad_server_salt');
    }

    public function getNewServerSalt(): string
    {
        $newSalt = $this->getTlMessage()->getValue('new_server_salt');

        return Packer::packLong($newSalt);
    }

    public function getBadMsdId(): string
    {
        return $this->getTlMessage()->getValue('bad_msg_id');
    }
}
