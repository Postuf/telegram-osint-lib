<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages\Update;

use TelegramOSINT\Exception\TGException;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Custom\UserStatus;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

class UpdateUserStatus extends TLServerMessage
{
    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    public static function isIt(AnonymousMessage $tlMessage): bool
    {
        return self::checkType($tlMessage, 'updateUserStatus');
    }

    public function getUserId(): int
    {
        return $this->getTlMessage()->getValue('user_id');
    }

    /**
     * @throws TGException
     *
     * @return UserStatus
     */
    public function getStatus(): UserStatus
    {
        $status = $this->getTlMessage()->getNode('status');

        return new UserStatus($status);
    }
}
