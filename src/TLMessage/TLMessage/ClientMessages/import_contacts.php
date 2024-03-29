<?php

/** @noinspection ContractViolationInspection */

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\Registration\NameGenerator\NameResource;
use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/contacts.importContacts
 */
class import_contacts implements TLClientMessage
{
    public const CONSTRUCTOR = 746589157;
    /** @see https://core.telegram.org/constructor/inputPhoneContact */
    public const INPUT_PHONE_CONTACT_CONSTRUCTOR = 4086478836;

    /**
     * @var string[]
     */
    private array $phones;
    /**
     * @var int
     */
    private static int $clientId = 0;

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

    public function getPhones(): array
    {
        return array_values(array_map(
            static function (string $item) { return str_replace('+', '', $item); },
            array_column($this->phones, 1)
        ));
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

    public function getName(): string
    {
        return 'import_contacts';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packVector($this->phones, $this->getElementGenerator());
    }

    private function getElementGenerator(): callable
    {
        return static function (array $clientIdAndPhone) {
            $human = new NameResource();
            $contactFirstName = $human->getName();
            $contactLastName = $human->getLastName();

            [$contactIdOnClient, $phone] = $clientIdAndPhone;

            return
                Packer::packConstructor(self::INPUT_PHONE_CONTACT_CONSTRUCTOR).
                Packer::packLong($contactIdOnClient).
                Packer::packString($phone).
                Packer::packString($contactFirstName).
                Packer::packString($contactLastName);
        };
    }

    private function transformPhone(string $phone): string
    {
        $phone = trim($phone);
        if ($phone[0] !== '+') {
            $phone = '+'.$phone;
        }

        return $phone;
    }
}
