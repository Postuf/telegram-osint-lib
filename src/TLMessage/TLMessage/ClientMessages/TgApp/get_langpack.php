<?php

namespace TLMessage\TLMessage\ClientMessages\TgApp;

use TLMessage\TLMessage\Packer;
use TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/langpack.getLangPack
 * note: current method has ctor 0xf2f2330a (layer 105)
 */
class get_langpack implements TLClientMessage
{

    const CONSTRUCTOR = -1699363442; // 0x9AB5C58E


    /**
     * @var string
     */
    private $langCode;


    /**
     * @var $langCode string
     */
    public function __construct(string $langCode)
    {
        $this->langCode = $langCode;
    }


    /**
     * @return string
     */
    public function getName()
    {
        return 'get_langpack';
    }


    /**
     * @return string
     */
    public function toBinary()
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packString($this->langCode);
    }

}