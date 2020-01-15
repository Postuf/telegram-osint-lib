<?php

namespace TelegramOSINT\Auth\Protocol;

use TelegramOSINT\TLMessage\TLMessage\ClientMessages\Shared\p_q_inner_data;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * AuthKey generation algorithm described in API docs
 */
class ApiAuthorization extends BaseAuthorization
{
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
        return new p_q_inner_data($pq, $p, $q, $oldClientNonce, $serverNonce, $newClientNonce);
    }
}
