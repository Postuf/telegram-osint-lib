<?php

namespace TLMessage\TLMessage\ServerMessages\Rpc;


use MTSerialization\AnonymousMessage;
use TLMessage\TLMessage\TLServerMessage;


class RpcError extends TLServerMessage
{

    /**
     * @param AnonymousMessage $tlMessage
     * @return boolean
     */
    public static function isIt(AnonymousMessage $tlMessage)
    {
        return self::checkType($tlMessage, 'rpc_error');
    }


    /**
     * @return string
     */
    public function getErrorString()
    {
        return $this->getTlMessage()->getValue('error_message');
    }


    /**
     * @return boolean
     */
    public function isNetworkMigrateError()
    {
        return strstr($this->getErrorString(), 'NETWORK_MIGRATE_');
    }


    /**
     * @return boolean
     */
    public function isPhoneMigrateError()
    {
        return strstr($this->getErrorString(), 'PHONE_MIGRATE_');
    }


    /**
     * @return boolean
     */
    public function isFloodError()
    {
        return strstr($this->getErrorString(), 'FLOOD_WAIT_');
    }


    /**
     * @return boolean
     */
    public function isUserDeactivated()
    {
        return strstr($this->getErrorString(), 'USER_DEACTIVATED');
    }


    /**
     * @return boolean
     */
    public function isPhoneBanned()
    {
        return strstr($this->getErrorString(), 'PHONE_NUMBER_BANNED');
    }


    /**
     * @return boolean
     */
    public function isPhoneNumberUnoccupied()
    {
        return strstr($this->getErrorString(), 'PHONE_NUMBER_UNOCCUPIED');
    }


    /**
     * @return boolean
     */
    public function isAuthKeyDuplicated()
    {
        return strstr($this->getErrorString(), 'AUTH_KEY_DUPLICATED');
    }


    /**
     * @return boolean
     */
    public function isSessionRevoked()
    {
        return strstr($this->getErrorString(), 'SESSION_REVOKED');
    }

}