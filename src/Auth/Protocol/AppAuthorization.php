<?php

namespace TelegramOSINT\Auth\Protocol;

use TelegramOSINT\TGConnection\DataCentre;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\p_q_inner_data_dc;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * AuthKey generation algorithm used by official application
 */
class AppAuthorization extends BaseAuthorization
{
    /** @var int */
    private $dcId;

    public function __construct(DataCentre $dc)
    {
        $this->dcId = $dc->getDcId();
        parent::__construct($dc);
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
    protected function getPqInnerDataMessage($pq, $p, $q, $oldClientNonce, $serverNonce, $newClientNonce)
    {
        return new p_q_inner_data_dc($pq, $p, $q, $oldClientNonce, $serverNonce, $newClientNonce, $this->dcId);
    }
}
