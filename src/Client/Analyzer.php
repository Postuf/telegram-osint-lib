<?php

namespace TelegramOSINT\Client;

use TelegramOSINT\MTSerialization\AnonymousMessage;

interface Analyzer
{
    /**
     * @param AnonymousMessage $message
     */
    public function analyzeMessage(AnonymousMessage $message): void;
}
