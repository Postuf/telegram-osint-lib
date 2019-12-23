<?php

namespace TLMessage\TLMessage\ClientMessages\Api;

use TLMessage\TLMessage\Packer;
use TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://git.mel.vin/telegram_desktop/upstream/blob/4221fe666f13f1d83c27c5f64e83d9bc8b3cfb51/README.md
 */
class import_contacts implements TLClientMessage
{

    const CONSTRUCTOR = 0xda30b32d;
    /** @see https://core.telegram.org/constructor/inputPhoneContact */
    const INPUT_PHONE_CONTACT_CONSTRUCTOR = -208488460; // 0xf392b7f4


    /**
     * @var string[]
     */
    private $phones;
    /**
     * @var bool
     */
    private $overwriteOnServer;


    /**
     * @param array $phones
     * @param bool $clearPrevious
     */
    public function __construct(array $phones, bool $clearPrevious)
    {
        $this->phones = $phones;
        $this->overwriteOnServer = $clearPrevious;
    }


    /**
     * @return string
     */
    public function getName()
    {
        return 'import_contacts';
    }


    /**
     * @return string
     */
    public function toBinary()
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packVector($this->phones, $this->getElementGenerator()).
            Packer::packBool($this->overwriteOnServer);
    }


    /**
     * @return callable
     */
    private function getElementGenerator()
    {
        return function ($phone) {

            $contactIdOnClient = unpack('Q', openssl_random_pseudo_bytes(8))[1];
            //$contactIdOnClient = \LibConfig::CLIENT_ID;
            $contactFirstName = 'name_'.$phone;
            $contactLastName = 'l_'.bin2hex(openssl_random_pseudo_bytes(5));

            return
                Packer::packConstructor(self::INPUT_PHONE_CONTACT_CONSTRUCTOR).
                Packer::packLong($contactIdOnClient).
                Packer::packString($phone).
                Packer::packString($contactFirstName).
                Packer::packString($contactLastName);
        };
    }

}