<?php

namespace TLMessage\TLMessage\ClientMessages\TgApp;

use TLMessage\TLMessage\Packer;
use TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://github.com/damang2/coin/blob/master/telethon/tl/functions/account.py#L648
 */
class register_device_push_java implements TLClientMessage
{
    const CONSTRUCTOR = 1555998096; // 0x5CBEA590

    /**
     * @return string
     */
    public function getName()
    {
        return 'register_device_push_java';
    }

    /**
     * @return string
     */
    public function toBinary()
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packInt(2).
            Packer::packString('eJw6Qq8aGB8:APA91bGmp2tE0mGtW2zs-zk-rOoOAV_ggVeDCAojNuUd3A4Elf47vJKUSWClmJEAb6xae2wuIGZzVSwLvptEmQ9lPZIevuTf_Jbo3RMdXXq_Tebcfh3r9ioVhYRqkGH-sygjXj9e6YES').
            Packer::packBool(false).
            Packer::packString(openssl_random_pseudo_bytes(256)).
            Packer::packVector([], function ($uid) {
                return Packer::packInt($uid);
            });
    }
}
