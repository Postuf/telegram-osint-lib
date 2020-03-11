<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages\Contact;

use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

class ContactClient extends TLServerMessage
{
    public function __construct(AnonymousMessage $tlMessage)
    {
        parent::__construct($tlMessage);
    }

    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    public static function isIt(AnonymousMessage $tlMessage): bool
    {
        return self::checkType($tlMessage, 'importedContact');
    }

    public function getUserId(): int
    {
        return $this->getTlMessage()->getValue('user_id');
    }

    public function getClientId(): int
    {
        return $this->getTlMessage()->getValue('client_id');
    }
}
