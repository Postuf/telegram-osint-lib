<?php

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages\Rpc;

use TelegramOSINT\Exception\TGException;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

class RpcError extends TLServerMessage
{
    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
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
     * @return bool
     */
    public function isNetworkMigrateError()
    {
        return strstr($this->getErrorString(), 'NETWORK_MIGRATE_');
    }

    /**
     * @return bool
     */
    public function isPhoneMigrateError()
    {
        return strstr($this->getErrorString(), 'PHONE_MIGRATE_');
    }

    /**
     * @return bool
     */
    public function isFloodError()
    {
        return strstr($this->getErrorString(), 'FLOOD_WAIT_');
    }

    /**
     * @return bool
     */
    public function isUserDeactivated()
    {
        return strstr($this->getErrorString(), 'USER_DEACTIVATED');
    }

    /**
     * @return bool
     */
    public function isPhoneBanned()
    {
        return strstr($this->getErrorString(), 'PHONE_NUMBER_BANNED');
    }

    /**
     * @return bool
     * @noinspection PhpUnused
     */
    public function isPhoneNumberUnoccupied()
    {
        return strstr($this->getErrorString(), 'PHONE_NUMBER_UNOCCUPIED');
    }

    /**
     * @return bool
     */
    public function isAuthKeyDuplicated()
    {
        return strstr($this->getErrorString(), 'AUTH_KEY_DUPLICATED');
    }

    /**
     * @return bool
     */
    public function isAuthKeyUnregistered()
    {
        return strstr($this->getErrorString(), 'AUTH_KEY_UNREGISTERED');
    }

    /**
     * @return bool
     */
    public function isSessionRevoked()
    {
        return strstr($this->getErrorString(), 'SESSION_REVOKED');
    }

    /**
     * @param AnonymousMessage $anonymousMessage
     *
     * @throws TGException
     */
    protected function throwIfIncorrectType(AnonymousMessage $anonymousMessage)
    {
        if(!static::isIt($anonymousMessage)) {
            $msg = $anonymousMessage->getType().' with error "'.$this->getErrorString().'" instead of '.get_called_class().' class';

            throw new TGException(TGException::ERR_TL_MESSAGE_UNEXPECTED_OBJECT, $msg);
        }
    }
}
