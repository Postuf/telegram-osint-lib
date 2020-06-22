<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\LibConfig;
use TelegramOSINT\Registration\AccountInfo;
use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/initConnection
 */
class init_connection implements TLClientMessage
{
    const CONSTRUCTOR = 3251461801; // 0xC1CD5EA9

    /**
     * @var AccountInfo
     */
    private $account;
    /**
     * @var TLClientMessage
     */
    private $query;

    /**
     * @param AccountInfo          $authInfo
     * @param TLClientMessage|null $query
     */
    public function __construct(AccountInfo $authInfo, TLClientMessage $query = null)
    {
        $this->account = $authInfo;
        $this->query = $query;
    }

    public function getName(): string
    {
        return 'init_connection';
    }

    public function toBinary(): string
    {
        $flags = 1026;

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
            $this->getParams()->toBinary().
            Packer::packBytes($this->query->toBinary());
    }

    private function getParams(): json_object
    {
        $data = new json_object_value(
            'data',
            new json_object_value_string(LibConfig::APP_CERT_SHA256)
        );
        $tz_offset = new json_object_value(
            'tz_offset',
            new json_object_value_number(LibConfig::APP_TZ_START)
        );

        return new json_object([
            $data,
            $tz_offset,
        ]);

    }
}
