<?php

declare(strict_types=1);

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
    public static function isIt(AnonymousMessage $tlMessage): bool
    {
        return self::checkType($tlMessage, 'rpc_error');
    }

    public function getErrorString(): string
    {
        return (string) $this->getTlMessage()->getValue('error_message');
    }

    public function isNetworkMigrateError(): bool
    {
        return (bool) strstr($this->getErrorString(), 'NETWORK_MIGRATE_');
    }

    public function isPhoneMigrateError(): bool
    {
        return (bool) strstr($this->getErrorString(), 'PHONE_MIGRATE_');
    }

    public function isFloodError(): bool
    {
        return (bool) strstr($this->getErrorString(), 'FLOOD_WAIT_');
    }

    public function isUserDeactivated(): bool
    {
        return (bool) strstr($this->getErrorString(), 'USER_DEACTIVATED');
    }

    public function isPhoneBanned(): bool
    {
        return (bool) strstr($this->getErrorString(), 'PHONE_NUMBER_BANNED');
    }

    /**
     * @noinspection PhpUnused
     * @noinspection UnknownInspectionInspection
     */
    public function isPhoneNumberUnoccupied(): bool
    {
        return (bool) strstr($this->getErrorString(), 'PHONE_NUMBER_UNOCCUPIED');
    }

    public function isAuthKeyDuplicated(): bool
    {
        return (bool) strstr($this->getErrorString(), 'AUTH_KEY_DUPLICATED');
    }

    public function isAuthKeyUnregistered(): bool
    {
        return (bool) strstr($this->getErrorString(), 'AUTH_KEY_UNREGISTERED');
    }

    public function isSessionRevoked(): bool
    {
        return (bool) strstr($this->getErrorString(), 'SESSION_REVOKED');
    }

    /**
     * @param AnonymousMessage $anonymousMessage
     *
     * @throws TGException
     */
    protected function throwIfIncorrectType(AnonymousMessage $anonymousMessage): void
    {
        if(!static::isIt($anonymousMessage)) {
            $msg = $anonymousMessage->getType().'" instead of '.static::class.' class';

            throw new TGException(TGException::ERR_TL_MESSAGE_UNEXPECTED_OBJECT, $msg);
        }
    }
}
