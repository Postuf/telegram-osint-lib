<?php

declare(strict_types=1);

namespace TGConnection\SocketMessenger;

use Client\AuthKey\AuthKey;
use MTSerialization\AnonymousMessage;
use MTSerialization\OwnImplementation\OwnAnonymousMessage;
use TGConnection\Socket\NullSocket;
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

    protected function readMessageFromSocket()
    {
        /*
         * Block new reads.
         *
         * Messages must be processed one in time, because otherwise
         * multi exception situation could occur
         */
        if(!empty($this->messagesToBeProcessedQueue)) {
            $this->processServiceMessage(array_shift($this->messagesToBeProcessedQueue));

            return;
        }

        if (!$this->trace[1]) {
            return;
        }

        if ($this->trace[1]) {
            foreach ($this->trace[1] as $k => $v) {
                // do not return message if not ready in time
                if (microtime(true) - $this->timeOffset <= $v[1]) {
                    return;
                }
                unset($this->trace[1][$k]);
                /** @var AnonymousMessage $msg */
                $msg = unserialize(hex2bin($v[1]));
                $arrMsg = (array) $msg;
                $arrMsg = reset($arrMsg);
                $packedMsg = new OwnAnonymousMessage([
                    '_'          => 'rpc_result',
                    'req_msg_id' => array_shift($this->msgIds),
                    'result'     => $arrMsg,
                ]);
                $this->processServiceMessage($packedMsg);
                break;
            }
        }
    }
}
