<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages;

interface PhotoInterface
{
    /**
     * @return FileLocation
     */
    public function getBigPhoto(): FileLocation;

    /**
     * @return FileLocation
     */
    public function getSmallPhoto(): FileLocation;

    /**
     * @return int
     */
    public function getDcId(): int;
}
