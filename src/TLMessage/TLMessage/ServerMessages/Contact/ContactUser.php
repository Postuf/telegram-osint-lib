<?php

namespace TLMessage\TLMessage\ServerMessages\Contact;

use Exception\TGException;
use MTSerialization\AnonymousMessage;
use TLMessage\TLMessage\ServerMessages\Custom\UserStatus;
use TLMessage\TLMessage\ServerMessages\UserProfilePhoto;
use TLMessage\TLMessage\TLServerMessage;

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

            return new UserProfilePhoto($photo);
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
}
