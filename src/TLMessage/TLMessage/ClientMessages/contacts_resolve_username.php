<?php

/** @noinspection SpellCheckingInspection */
declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/contacts.resolveUsername
 */
class contacts_resolve_username implements TLClientMessage
{
    const CONSTRUCTOR = -113456221; // 0xf93ccba3

    /** @var string */
    private $username;

    /**
     * @param string $username
     */
    public function __construct(string $username)
    {
        $this->username = $username;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'contacts_resolve_username';
    }

    /**
     * {@inheritdoc}
     */
    public function toBinary()
    {
        return Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packString($this->username);
    }
}
