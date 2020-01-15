<?php

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages\TgApp;

use TelegramOSINT\Registration\NameGenerator\NameResource;
use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/contacts.importContacts
 */
class import_contacts implements TLClientMessage
{
    const CONSTRUCTOR = 746589157; // 0x2C800BE5
    /** @see https://core.telegram.org/constructor/inputPhoneContact */
    const INPUT_PHONE_CONTACT_CONSTRUCTOR = -208488460; // 0xf392b7f4

    /**
     * @var string[]
     */
    private $phones;
    /**
     * @var int
     */
    private static $clientId = 0;

    /**
     * @param array $phones
     */
    public function __construct(array $phones)
    {
        foreach ($phones as $phone) {
            self::$clientId++;
            $this->phones[self::$clientId] = [self::$clientId, $this->transformPhone($phone)];
        }
    }

    private function transformPhone($phone)
    {
        $phone = trim((string) $phone);
        if($phone[0] != '+')
            $phone = '+'.$phone;

        return $phone;
    }

    /**
     * @param int $clientId
     *
     * @return string|bool
     */
    public function getPhoneByClientId(int $clientId)
    {
        return isset($this->phones[$clientId]) ?
            $this->phones[$clientId][1] :
            false;
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
            Packer::packVector($this->phones, $this->getElementGenerator());
    }

    /**
     * @return callable
     */
    private function getElementGenerator()
    {
        return function (array $clientIdAndPhone) {

            $human = new NameResource();
            $contactFirstName = $human->getName();
            $contactLastName = $human->getLastName();

            $contactIdOnClient = $clientIdAndPhone[0];
            $phone = $clientIdAndPhone[1];

            return
                Packer::packConstructor(self::INPUT_PHONE_CONTACT_CONSTRUCTOR).
                Packer::packLong($contactIdOnClient).
                Packer::packString($phone).
                Packer::packString($contactFirstName).
                Packer::packString($contactLastName);
        };
    }
}
