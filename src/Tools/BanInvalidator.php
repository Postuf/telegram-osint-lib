<?php

declare(strict_types=1);

namespace TelegramOSINT\Tools;

use TelegramOSINT\Exception\TGException;

class BanInvalidator implements CacheInvalidator
{
    public function invalidateIfNeeded(TGException $e, Cache $cache): void
    {
        $code = $e->getCode();
        $banCodes = [
            TGException::ERR_MSG_BANNED_SESSION_STOLEN,
            TGException::ERR_MSG_BANNED_AUTHKEY_DUPLICATED,
            TGException::ERR_MSG_PHONE_BANNED,
            TGException::ERR_MSG_USER_BANNED,
        ];
        if (in_array($code, $banCodes, true)) {
            $cache->del();
        }
    }
}
