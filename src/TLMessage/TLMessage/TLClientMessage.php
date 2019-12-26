<?php

namespace TLMessage\TLMessage;

interface TLClientMessage
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function toBinary();
}
