<?php

declare(strict_types=1);

namespace TelegramOSINT\Client\AuthKey;

use TelegramOSINT\Registration\AccountInfo;

interface AuthorizedAuthKey extends AuthKey
{
    /**
     * @return string
     */
    public function getPhone(): string;

    /**
     * @return AccountInfo
     */
    public function getAccountInfo(): AccountInfo;
}
