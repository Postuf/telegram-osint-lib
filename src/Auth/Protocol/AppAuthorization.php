<?php

declare(strict_types=1);

namespace TelegramOSINT\Auth\Protocol;

use TelegramOSINT\Logger\ClientDebugLogger;
use TelegramOSINT\TGConnection\DataCentre;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\p_q_inner_data_dc;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;
use TelegramOSINT\Tools\Proxy;

/**
 * AuthKey generation algorithm used by official application
 */
class AppAuthorization extends BaseAuthorization
{
    /** @var int */
    private int $dcId;

    public function __construct(DataCentre $dc, ?Proxy $proxy = null, ?ClientDebugLogger $logger = null)
    {
        $this->dcId = $dc->getDcId();
        parent::__construct($dc, $proxy, $logger);
    }

    protected function getPqInnerDataMessage(
        int $pq,
        int $p,
        int $q,
        string $oldClientNonce,
        string $serverNonce,
        string $newClientNonce
    ): TLClientMessage {
        return new p_q_inner_data_dc($pq, $p, $q, $oldClientNonce, $serverNonce, $newClientNonce, $this->dcId);
    }
}
