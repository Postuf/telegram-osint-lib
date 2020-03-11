<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages;

use TelegramOSINT\Exception\TGException;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Custom\UserStatus;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

class UserSelf extends TLServerMessage
{
    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    public static function isIt(AnonymousMessage $tlMessage): bool
    {
        return self::checkType($tlMessage, 'userSelf');
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
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (TGException $e){
            return null;
        }

        return new UserStatus($status);
    }

    /**
     * @throws TGException
     *
     * @return PhotoInterface|null
     */
    public function getPhoto(): ?PhotoInterface
    {
        $photo = $this->getTlMessage()->getNode('photo');
        if (TLServerMessage::checkType($photo, 'userProfilePhoto')) {
            return new UserProfilePhoto($photo);
        } elseif (TLServerMessage::checkType($photo, 'chatPhoto')) {
            return new ChatPhoto($photo);
        } else {
            throw new TGException(TGException::ERR_DESERIALIZER_UNKNOWN_OBJECT);
        }
    }

    public function getUserId(): int
    {
        return $this->getTlMessage()->getValue('id');
    }

    public function getPhone(): string
    {
        return $this->getTlMessage()->getValue('phone');
    }
}
