<?php

namespace TelegramOSINT\MTSerialization;

interface MTDeserializer
{
    public function deserialize(string $data): AnonymousMessage;
}
