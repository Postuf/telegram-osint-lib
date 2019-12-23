<?php

namespace TLMessage\TLMessage\ClientMessages\TgApp;

use TLMessage\TLMessage\Packer;
use TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/help.getTermsOfServiceUpdate
 */
class get_terms_of_service_update implements TLClientMessage
{

    const CONSTRUCTOR = 749019089; // 0x2CA51FD1


    /**
     * @return string
     */
    public function getName()
    {
        return 'get_terms_of_service_update';
    }


    /**
     * @return string
     */
    public function toBinary()
    {
        return Packer::packConstructor(self::CONSTRUCTOR);
    }


}