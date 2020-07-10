<?php

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages;

use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

class DcConfigApp extends TLServerMessage
{
    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    public static function isIt(AnonymousMessage $tlMessage): bool
    {
        return self::checkType($tlMessage, 'config');
    }

    /**
     * @return DcOption[]
     * @noinspection PhpDocMissingThrowsInspection
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function getDataCenters(): array
    {
        $dcs = $this->getTlMessage()->getNodes('dc_options');

        $dcObjects = [];
        foreach ($dcs as $dc) {
            $dcObjects[] = new DcOption($dc);
        }

        return $dcObjects;
    }

    /**
     * @noinspection PhpUnused
     * @noinspection UnknownInspectionInspection
     */
    public function getLangPackVersion(): int
    {
        return (int) $this->getTlMessage()->getValue('lang_pack_version');
    }
}
