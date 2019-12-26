<?php

namespace Auth\Protocol;

use TGConnection\DataCentre;
use TLMessage\TLMessage\ClientMessages\TgApp\p_q_inner_data_dc;
use TLMessage\TLMessage\TLClientMessage;

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
