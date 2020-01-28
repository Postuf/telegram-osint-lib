<?php

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages\Contact;

use TelegramOSINT\Exception\TGException;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Custom\UserStatus;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\UserProfilePhoto;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

/**
 * @see https://core.telegram.org/type/User
 */
class ContactUser extends TLServerMessage
{
    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    public static function isIt(AnonymousMessage $tlMessage)
    {
        return self::checkType($tlMessage, 'user');
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
     * @return UserProfilePhoto
     */
    public function getPhoto()
    {
        try {
            $photo = $this->getTlMessage()->getNode('photo');

            if (TLServerMessage::checkType($photo, 'userProfilePhoto')) {
                return new UserProfilePhoto($photo);
            } elseif (TLServerMessage::checkType($photo, 'chatPhoto')) {
                return new ChatPhoto($photo);
            } else {
                throw new TGException(TGException::ERR_DESERIALIZER_UNKNOWN_OBJECT);
            }
        } catch (TGException $e){
            if($e->getCode() == TGException::ERR_TL_MESSAGE_FIELD_BAD_NODE)
                return null;
            else
                throw $e;
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

    /**
     * @throws TGException
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->getTlMessage()->getValue('username');
    }

    /**
     * @return int
     */
    public function getAccessHash()
    {
        return $this->getTlMessage()->getValue('access_hash');
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->getTlMessage()->getValue('first_name');
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->getTlMessage()->getValue('last_name');
    }
}
