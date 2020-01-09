<?php

declare(strict_types=1);

namespace TLMessage\TLMessage\ClientMessages\TgApp;

use TLMessage\TLMessage\Packer;
use TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/help.getDeepLinkInfo
 */
class get_deeplink_info implements TLClientMessage
{
    const CONSTRUCTOR = 1072547679; // 0x3fedc75f

    /** @var string */
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'get_deeplink_info';
    }

    /**
     * {@inheritdoc}
     */
    public function toBinary()
    {
        return Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packString($this->path);
    }
}
