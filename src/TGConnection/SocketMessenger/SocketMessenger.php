<?php


namespace TGConnection\SocketMessenger;

use LibConfig;
use MTSerialization\AnonymousMessage;
use TGConnection\DataCentre;
use TLMessage\TLMessage\TLClientMessage;

/**
 * Telegram-specific socket interface
 */
interface SocketMessenger
{

    /**
     * @return AnonymousMessage
     */
    public function readMessage();

    /**
     * @return void
     */
    public function terminate();

    /**
     * @param TLClientMessage $payload
     */
    public function writeMessage(TLClientMessage $payload);

    /**
     * @param TLClientMessage $message
     * @param int $timeoutMs
     * @return AnonymousMessage
     */
    public function getResponse(TLClientMessage $message, $timeoutMs = LibConfig::CONN_SOCKET_TIMEOUT_WAIT_RESPONSE_MS);

    /**
     * @param TLClientMessage $message
     * @param callable $onAsyncResponse
     * @return void
     */
    public function getResponseAsync(TLClientMessage $message, callable $onAsyncResponse);

    /**
     * @return DataCentre
     */
    public function getDCInfo();

}