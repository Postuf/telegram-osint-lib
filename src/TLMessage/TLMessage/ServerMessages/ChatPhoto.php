<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages;

use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

class ChatPhoto extends TLServerMessage implements PhotoInterface
{
    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    public static function isIt(AnonymousMessage $tlMessage): bool
    {
        return self::checkType($tlMessage, 'chatPhoto');
    }

    /**
     * @return FileLocation
     */
    public function getBigPhoto(): FileLocation
    {
        $photo = $this->getTlMessage()->getNode('photo_big');

        return new FileLocation($photo);
    }

    /**
     * @return FileLocation
     */
    public function getSmallPhoto(): FileLocation
    {
        $photo = $this->getTlMessage()->getNode('photo_small');

        return new FileLocation($photo);
    }

    /**
     * @return int
     */
    public function getDcId(): int
    {
        return $this->getTlMessage()->getValue('dc_id');
    }
}
