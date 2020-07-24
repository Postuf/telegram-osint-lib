<?php
declare(strict_types=1);

namespace TelegramOSINT\Exception;

use TelegramOSINT\TGConnection\DataCentre;

class MigrateException extends TGException
{
    /** @var int */
    private int $dcId;
    /** @var DataCentre|null */
    private ?DataCentre $dc;

    public function __construct(
        int $dcId,
        int $code = 0,
        $clarification = "",
        ?DataCentre $dc = null
    ) {
        parent::__construct($code, $clarification);
        $this->dcId = $dcId;
        $this->dc = $dc;
    }

    public function getDcId(): int
    {
        return $this->dcId;
    }

    public function getDC(): ?DataCentre
    {
        return $this->dc;
    }
}