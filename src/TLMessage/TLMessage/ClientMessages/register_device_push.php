<?php

/** @noinspection PhpUnused
 * @noinspection UnknownInspectionInspection
 */

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\Exception\TGException;
use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/account.registerDevice
 */
class register_device_push implements TLClientMessage
{
    private const CONSTRUCTOR = 3968205178; // 0xec86017a

    public function getName(): string
    {
        return 'register_device_push';
    }

    /**
     * @param int $length
     *
     * @throws TGException
     *
     * @return string
     */
    private static function createSecret(): string
    {
        /** @noinspection CryptographicallySecureRandomnessInspection */
        $secret = openssl_random_pseudo_bytes(256, $strong);
        if ($secret === false || $strong === false) {
            throw new TGException(TGException::ERR_CRYPTO_INVALID);
        }

        return $secret;
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
                Packer::packInt(0). // flags , no_muted=false
                Packer::packInt(2). // token_type, 2 - FCM (firebase token for google firebase)
                Packer::packString('eJw6Qq8aGB8:APA91bGmp2tE0mGtW2zs-zk-rOoOAV_ggVeDCAojNuUd3A4Elf47vJKUSWClmJEAb6xae2wuIGZzVSwLvptEmQ9lPZIevuTf_Jbo3RMdXXq_Tebcfh3r9ioVhYRqkGH-sygjXj9e6YES'). // token
                Packer::packBool(false). // app_sandbox
                Packer::packString(self::createSecret()). // secret
                Packer::packVector([], static function ($uid) { // other_uids
                    return Packer::packInt($uid);
                });
    }
}
