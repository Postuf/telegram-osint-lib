<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

class channel_participants_filter implements TLClientMessage
{
    public const PARTICIPANTS_RECENT = 3728686201;
    public const PARTICIPANTS_ADMINS = 3026225513;
    public const PARTICIPANTS_BOTS = 2966521435;

    public const PARTICIPANTS_KICKED = 2746567045;
    public const PARTICIPANTS_BANNED = 338142689;
    public const PARTICIPANTS_SEARCH = 106343499;
    public const PARTICIPANTS_CONTACTS = 3144345741;

    public const PARTICIPANTS_MENTIONS = 3763035371;


    /** @var int */
    private int $constructor;
    /** @var string|null */
    private ?string $query;
    /** @var int|null */
    private ?int $topMsgId;

    public function __construct(int $constructor = self::PARTICIPANTS_RECENT, ?string $query = null, ?int $topMsgId = null)
    {
        $this->constructor = $constructor;
        $this->query = $query;
        $this->topMsgId = $topMsgId;
    }

    public function getName(): string
    {
        return 'channel_participants_filter';
    }

    public function toBinary(): string
    {
        $binary = Packer::packConstructor($this->constructor);
        if ($this->constructor === self::PARTICIPANTS_MENTIONS) {
            $flags = 0;
            if ($this->query !== null) {
                $flags |= 1;
            }
            if ($this->topMsgId !== null) {
                $flags |= 2;
            }
            $binary .= Packer::packInt($flags);
            if ($flags & 1) {
                $binary .= Packer::packString($this->query);
            }
            if ($flags & 2) {
                $binary .= Packer::packInt($this->topMsgId);
            }
        } elseif (!in_array($this->constructor, [self::PARTICIPANTS_RECENT, self::PARTICIPANTS_ADMINS, self::PARTICIPANTS_BOTS])) {
            $binary .= Packer::packString($this->query);
        }

        return $binary;
    }
}
