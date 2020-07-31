<?php

namespace TelegramOSINT\TGConnection;

use TelegramOSINT\LibConfig;

class DataCentre
{
    /** @var string */
    private string $dcIp;
    /** @var int */
    private int $dcId;
    /** @var int */
    private int $dcPort;

    /**
     * @param string $dcIp
     * @param int    $dcId
     * @param int    $dcPort
     */
    public function __construct($dcIp, $dcId, $dcPort)
    {
        $this->dcIp = $dcIp;
        $this->dcId = $dcId;
        $this->dcPort = $dcPort;
    }

    public static function getDefault(): self
    {
        return new self(
            LibConfig::DC_DEFAULT_IP,
            LibConfig::DC_DEFAULT_ID,
            LibConfig::DC_DEFAULT_PORT
        );
    }

    public function getDcIp(): string
    {
        return $this->dcIp;
    }

    public function getDcId(): int
    {
        return $this->dcId;
    }

    public function getDcPort(): int
    {
        return $this->dcPort;
    }
}
