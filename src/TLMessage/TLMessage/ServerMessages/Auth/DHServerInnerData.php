<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages\Auth;

use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

class DHServerInnerData extends TLServerMessage
{
    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    public static function isIt(AnonymousMessage $tlMessage): bool
    {
        return self::checkType($tlMessage, 'server_DH_inner_data');
    }

    public function getG()
    {
        $g = $this->getTlMessage()->getValue('g');

        return Packer::packIntAsBytesLittleEndian($g);
    }

    /**
     * @noinspection PhpUnused
     */
    public function getDHPrime(): string
    {
        return $this->getTlMessage()->getValue('dh_prime');
    }

    public function getGA(): string
    {
        return $this->getTlMessage()->getValue('g_a');
    }
}
