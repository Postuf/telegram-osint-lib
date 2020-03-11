<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages\Contact;

use TelegramOSINT\Exception\TGException;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Custom\UserStatus;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

class ContactStatus extends TLServerMessage
{
    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    public static function isIt(AnonymousMessage $tlMessage): bool
    {
        return self::checkType($tlMessage, 'contactStatus');
    }

    /**
     * @throws TGException
     *
     * @return UserStatus|null
     */
    public function getStatus(): ?UserStatus
    {
        try {
            $status = $this->getTlMessage()->getNode('status');
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (TGException $e) {
            return null;
        }

        return new UserStatus($status);
    }

    public function getUserId(): int
    {
        return $this->getTlMessage()->getValue('user_id');
    }
}
