<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/channels.getParticipants
 */
class get_participants implements TLClientMessage
{
    private const CONSTRUCTOR = 306054633; // 0x123e05e9
    private const LIMIT = 100;

    /** @var int */
    private $offset;
    /** @var input_channel */
    private $channel;
    /** @var string|null */
    private $query;

    public function __construct(input_channel $channel, int $offset = 0, ?string $query = null)
    {
        $this->channel = $channel;
        $this->offset = $offset;
        $this->query = $query;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'get_participants';
    }

    /**
     * @return string
     */
    public function toBinary()
    {
        return Packer::packConstructor(self::CONSTRUCTOR).
            $this->channel->toBinary().
            ($this->query
                ? new channel_participants_filter(channel_participants_filter::PARTICIPANTS_SEARCH, $this->query)
                : new channel_participants_filter())->toBinary().
            Packer::packInt($this->offset).
            Packer::packInt(self::LIMIT).
            Packer::packInt(0); // hash
    }
}
