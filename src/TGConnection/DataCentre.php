<?php

namespace TGConnection;

use LibConfig;

class DataCentre
{
    /** @var string */
    private $dcIp;
    /** @var int */
    private $dcId;
    /** @var int */
    private $dcPort;

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

    /**
     * @return DataCentre
     */
    public static function getDefault()
    {
        return new self(
            LibConfig::DC_DEFAULT_IP,
            LibConfig::DC_DEFAULT_ID,
            LibConfig::DC_DEFAULT_PORT);
    }

    /**
     * @return string
     */
    public function getDcIp(): string
    {
        return $this->dcIp;
    }

    /**
     * @return int
     */
    public function getDcId(): int
    {
        return $this->dcId;
    }

    /**
     * @return int
     */
    public function getDcPort(): int
    {
        return $this->dcPort;
    }
}
