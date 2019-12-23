<?php

namespace TLMessage\TLMessage\ClientMessages\TgApp;

use TLMessage\TLMessage\Packer;
use TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/contacts.search
 */
class contacts_search implements TLClientMessage
{

    const CONSTRUCTOR = 301470424; // 0x11F812D8

    const DEFAULT_APP_LIMIT = 50;

    /**
     * @var string
     */
    private $nick;
    /**
     * @var int
     */
    private $limit;


    public function __construct(string $nickName, int $limit = self::DEFAULT_APP_LIMIT)
    {
        $this->nick = $nickName;
        $this->limit = $limit;
    }


    /**
     * @return string
     */
    public function getName()
    {
        return 'contacts_search';
    }


    /**
     * @return string
     */
    public function toBinary()
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packString($this->nick).
            Packer::packInt($this->limit);
    }

}