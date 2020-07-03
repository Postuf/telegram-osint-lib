<?php

/** @noinspection PhpUnused */

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\Exception\TGException;
use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://github.com/damang2/coin/blob/master/telethon/tl/functions/account.py#L648
 */
class register_device_push_java implements TLClientMessage
{
    private const CONSTRUCTOR = 1555998096; // 0x5CBEA590

    public function getName(): string
    {
        return 'register_device_push_java';
    }

    /**
     * @param int $length
     *
     * @throws TGException
     *
     * @return string
     */
    private static function createId(int $length): string
    {
        /** @noinspection CryptographicallySecureRandomnessInspection */
        $id = openssl_random_pseudo_bytes($length, $strong);
        if ($id === false || $strong === false) {
            throw new TGException(TGException::ERR_CRYPTO_INVALID);
        }

        return $id;
    }

    /**
     * @throws TGException
     *
     * @return string
     */
    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packInt(2).
            Packer::packString('eJw6Qq8aGB8:APA91bGmp2tE0mGtW2zs-zk-rOoOAV_ggVeDCAojNuUd3A4Elf47vJKUSWClmJEAb6xae2wuIGZzVSwLvptEmQ9lPZIevuTf_Jbo3RMdXXq_Tebcfh3r9ioVhYRqkGH-sygjXj9e6YES').
            Packer::packBool(false).
            Packer::packString(self::createId(256)).
            Packer::packVector([], static function ($uid) {
                return Packer::packInt($uid);
            });
    }
}
