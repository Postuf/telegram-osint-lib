<?php

namespace TLMessage\TLMessage\ClientMessages\TgApp;

use LibConfig;
use Registration\AccountInfo;
use TLMessage\TLMessage\Packer;
use TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/initConnection
 */
class init_connection implements TLClientMessage
{

    const CONSTRUCTOR = 2018609336; // 0x785188B8


    /**
     * @var AccountInfo
     */
    private $account;
    /**
     * @var TLClientMessage
     */
    private $query;


    /**
     *
     * @param AccountInfo $authInfo
     * @var $query TLClientMessage
     */
    public function __construct(AccountInfo $authInfo, TLClientMessage $query = null)
    {
        $this->account = $authInfo;
        $this->query = $query;
    }


    /**
     * @return string
     */
    public function getName()
    {
        return 'init_connection';
    }


    /**
     * @return string
     */
    public function toBinary()
    {
        $flags = 0x0;

        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packInt($flags).
            Packer::packInt(LibConfig::APP_API_ID).
            Packer::packString($this->account->getDevice()).
            Packer::packString($this->account->getAndroidSdkVersion()).
            Packer::packString($this->account->getAppVersion().' ('.$this->account->getAppVersionCode().')').
            Packer::packString($this->account->getDeviceLang()).
            Packer::packString(LibConfig::APP_DEFAULT_LANG_PACK).
            Packer::packString($this->account->getAppLang()).
            Packer::packBytes($this->query->toBinary());
    }

}