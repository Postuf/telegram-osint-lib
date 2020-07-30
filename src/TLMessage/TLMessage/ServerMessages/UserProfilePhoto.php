<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages;

use TelegramOSINT\Exception\TGException;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

class UserProfilePhoto extends TLServerMessage implements PhotoInterface
{
    /**
     * @var bool
     */
    private $v1;

    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    public static function isIt(AnonymousMessage $tlMessage): bool
    {
        return
            self::checkType($tlMessage, 'userProfilePhoto_v1') ||
            self::checkType($tlMessage, 'userProfilePhoto');
    }

    public function __construct(AnonymousMessage $tlMessage)
    {
        parent::__construct($tlMessage);
        $this->v1 = self::checkType($tlMessage, 'userProfilePhoto_v1');
    }

    public function getPhotoId(): int
    {
        return $this->getTlMessage()->getValue('photo_id');
    }

    /**
     * @throws TGException
     *
     * @return FileLocation
     */
    public function getBigPhoto(): FileLocation
    {
        $photo = $this->getTlMessage()->getNode('photo_big');

        return new FileLocation($photo);
    }

    /**
     * @throws TGException
     *
     * @return FileLocation
     */
    public function getSmallPhoto(): FileLocation
    {
        $photo = $this->getTlMessage()->getNode('photo_small');

        return new FileLocation($photo);
    }

    /**
     * @throws TGException
     *
     * @return int
     */
    public function getDcId(): int
    {
        $this->throwIfNotV2();

        return $this->getTlMessage()->getValue('dc_id');
    }

    public function isV2(): bool
    {
        return !$this->v1;
    }

    /**
     * @throws TGException
     */
    private function throwIfNotV2(): void
    {
        if (!$this->isV2()) {
            throw new TGException(TGException::ERR_TL_MESSAGE_FIELD_NOT_EXISTS, 'Deprecated node!');
        }
    }
}
