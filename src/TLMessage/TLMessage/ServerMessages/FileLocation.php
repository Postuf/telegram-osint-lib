<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages;

use TelegramOSINT\Exception\TGException;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

class FileLocation extends TLServerMessage
{
    private bool $deprecated;

    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    public static function isIt(AnonymousMessage $tlMessage): bool
    {
        return
            self::checkType($tlMessage, 'fileLocation') ||
            self::checkType($tlMessage, 'fileLocationToBeDeprecated');
    }

    public function __construct(AnonymousMessage $tlMessage)
    {
        parent::__construct($tlMessage);
        $this->deprecated = self::checkType($tlMessage, 'fileLocationToBeDeprecated');
    }

    /**
     * @throws TGException
     *
     * @return int
     */
    public function getDcId(): int
    {
        $this->throwIfDeprecated();

        return $this->getTlMessage()->getValue('dc_id');
    }

    public function getVolumeId(): int
    {
        return $this->getTlMessage()->getValue('volume_id');
    }

    public function getLocalId(): int
    {
        return $this->getTlMessage()->getValue('local_id');
    }

    /**
     * @throws TGException
     *
     * @return string
     */
    public function getSecret(): string
    {
        $this->throwIfDeprecated();

        return $this->getTlMessage()->getValue('secret');
    }

    /**
     * @throws TGException
     *
     * @return string
     */
    public function getReference(): string
    {
        $this->throwIfDeprecated();

        return $this->getTlMessage()->getValue('file_reference');
    }

    /**
     * @throws TGException
     */
    private function throwIfDeprecated(): void
    {
        if ($this->deprecated) {
            throw new TGException(TGException::ERR_TL_MESSAGE_FIELD_NOT_EXISTS, 'Deprecated node!');
        }
    }
}
