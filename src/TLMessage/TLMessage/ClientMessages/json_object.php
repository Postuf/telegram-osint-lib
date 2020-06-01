<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

class json_object implements TLClientMessage
{
    private const CONSTRUCTOR = -1715350371;

    /** @var array */
    private $objects;

    public function __construct(array $objects)
    {
        $this->objects = $objects;
    }

    public function getName(): string
    {
        return 'json_objects';
    }

    public function toBinary(): string
    {
        return Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packVector($this->objects, function (json_object_value $jsonObject) {
               return $jsonObject->toBinary();
            });
    }
}
