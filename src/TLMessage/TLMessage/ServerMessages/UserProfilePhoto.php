<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages;

use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

class UserProfilePhoto extends TLServerMessage implements PhotoInterface
{
    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    public static function isIt(AnonymousMessage $tlMessage): bool
    {
        return self::checkType($tlMessage, 'userProfilePhoto');
    }

    public function __construct(AnonymousMessage $tlMessage)
    {
        parent::__construct($tlMessage);
    }

    public function getPhotoId(): int
    {
        return $this->getTlMessage()->getValue('photo_id');
    }

    public function getDcId(): int
    {
        return $this->getTlMessage()->getValue('dc_id');
    }
}
