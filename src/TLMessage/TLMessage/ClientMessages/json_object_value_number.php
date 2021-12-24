<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;

/**
 * https://core.telegram.org/constructor/jsonNumber
 */
class json_object_value_number extends json_object_value_abstract
{
    private const CONSTRUCTOR = 736157604;

    /** @var float */
    private float $value;

    public function __construct(float $value)
    {
        $this->value = $value;
    }

    public function getName(): string
    {
        return 'json_object_value_number';
    }

    public function toBinary(): string
    {
        return Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packDouble($this->value);
    }
}
