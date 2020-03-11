<?php

namespace TelegramOSINT\TLMessage\TLMessage;

interface TLClientMessage
{
    public function getName(): string;

    public function toBinary(): string;
}
