<?php

namespace TelegramOSINT\Auth\Protocol;

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

    public function __construct(DataCentre $dc, ?Proxy $proxy = null)
    {
        $this->dcId = $dc->getDcId();
        parent::__construct($dc, $proxy);
    }

    /**
     * @param int    $pq
     * @param int    $p
     * @param int    $q
     * @param string $oldClientNonce
     * @param string $serverNonce
     * @param string $newClientNonce
     *
     * @return TLClientMessage
     */
    protected function getPqInnerDataMessage($pq, $p, $q, $oldClientNonce, $serverNonce, $newClientNonce): TLClientMessage
    {
        return new p_q_inner_data_dc($pq, $p, $q, $oldClientNonce, $serverNonce, $newClientNonce, $this->dcId);
    }
}
