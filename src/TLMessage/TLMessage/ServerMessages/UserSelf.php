<?php

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
    public static function isIt(AnonymousMessage $tlMessage)
    {
        return self::checkType($tlMessage, 'userSelf');
    }

    /**
     * @return UserStatus|null
     */
    public function getStatus()
    {
        try {
            $status = $this->getTlMessage()->getNode('status');
        } catch (TGException $e){
            return null;
        }

        return new UserStatus($status);
    }

    /**
     * @return UserProfilePhoto|ChatPhoto|null
     */
    public function getPhoto()
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

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->getTlMessage()->getValue('id');
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->getTlMessage()->getValue('phone');
    }
}
