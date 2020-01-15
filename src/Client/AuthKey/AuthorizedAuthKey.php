<?php

namespace TelegramOSINT\Client\AuthKey;

use TelegramOSINT\Registration\AccountInfo;

interface AuthorizedAuthKey extends AuthKey
{
    /**
     * @return string
     */
    public function getPhone();

    /**
     * @return AccountInfo
     */
    public function getAccountInfo();
}
