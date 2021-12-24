<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;

/**
 * https://core.telegram.org/constructor/jsonString
 */
class json_object_value_string extends json_object_value_abstract
{
    private const CONSTRUCTOR = 3072226938;

    /** @var string */
    private string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function getName(): string
    {
        return 'json_object_value_string';
    }

    public function toBinary(): string
    {
        return Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packString($this->value);
    }
}
