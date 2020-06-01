<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * https://core.telegram.org/constructor/jsonObjectValue
 */
class json_object_value implements TLClientMessage
{
    private const CONSTRUCTOR = -1059185703; // 0xc0de1bd9

    /** @var string */
    private $key;
    /** @var json_object_value_abstract */
    private $value;

    public function __construct(string $key, json_object_value_abstract $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    public function getName(): string
    {
        return 'json_object_value';
    }

    public function toBinary(): string
    {
        return Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packString($this->key).$this->value->toBinary();
    }
}
