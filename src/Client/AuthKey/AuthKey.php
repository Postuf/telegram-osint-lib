<?php

namespace TelegramOSINT\Client\AuthKey;

use TelegramOSINT\TGConnection\DataCentre;

interface AuthKey
{
    /**
     * @return string
     */
    public function getRawAuthKey();

    /**
     * @return string
     */
    public function getSerializedAuthKey();

    /**
     * @return DataCentre
     */
    public function getAttachedDC();
}
