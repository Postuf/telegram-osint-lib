<?php

namespace Client;


use MTSerialization\AnonymousMessage;

interface Analyzer
{
    /**
     * @param AnonymousMessage $message
     */
    public function analyzeMessage(AnonymousMessage $message);
}