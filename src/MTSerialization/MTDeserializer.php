<?php

namespace TelegramOSINT\MTSerialization;

interface MTDeserializer
{
    /**
     * @param string $data
     *
     * @return AnonymousMessage
     */
    public function deserialize(string $data);
}
