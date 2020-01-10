<?php

declare(strict_types=1);

namespace Helpers;

use Client\AuthKey\AuthKey;
use MTSerialization\AnonymousMessage;
use MTSerialization\OwnImplementation\OwnAnonymousMessage;
use TGConnection\SocketMessenger\EncryptedSocketMessenger;
use TGConnection\SocketMessenger\MessageListener;
use TLMessage\TLMessage\TLClientMessage;

class TraceSocketMessenger extends EncryptedSocketMessenger
{
    /** @var float */
    private $timeOffset;

    /** @var array */
    private $trace;

    /** @var int[] */
    private $msgIds = [];

    /**
     * @param array           $trace    see tests/Integration/Scenario for
     * @param AuthKey         $authKey
     * @param MessageListener $callback
     */
    public function __construct(array $trace, AuthKey $authKey, MessageListener $callback)
    {
        parent::__construct(new NullSocket(), $authKey, $callback);
        $this->timeOffset = microtime(true) - $trace[0];
        $this->trace = $trace;
    }

    protected function writeIdentifiedMessage(TLClientMessage $payload, $messageId)
    {
        $this->msgIds[] = $messageId;
    }

    protected function readMessageFromSocket(): ?AnonymousMessage
    {
        if (!$this->trace[1]) {
            return null;
        }

        foreach ($this->trace[1] as $k => $v) {
            // do not return message if not ready in time
            if (microtime(true) - $this->timeOffset <= $v[1]) {
                return null;
            }
            unset($this->trace[1][$k]);
            /** @var AnonymousMessage $msg */
            $msg = unserialize(hex2bin($v[1]));
            $arrMsg = (array) $msg;
            $arrMsg = reset($arrMsg);
            return new OwnAnonymousMessage([
                '_'          => 'rpc_result',
                'req_msg_id' => array_shift($this->msgIds),
                'result'     => $arrMsg,
            ]);
        }

        return null;
    }
}
