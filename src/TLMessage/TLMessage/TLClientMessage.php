<?php

namespace TelegramOSINT\TLMessage\TLMessage;

interface TLClientMessage
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function toBinary(): string;
}
