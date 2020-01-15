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
     * @return UserProfilePhoto|null
     */
    public function getPhoto()
    {
        try {
            $photo = $this->getTlMessage()->getNode('photo');

            return new UserProfilePhoto($photo);
        }catch (TGException $e){
            return null;
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
