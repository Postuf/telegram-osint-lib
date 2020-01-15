<?php

declare(strict_types=1);

namespace Helpers;

use TelegramOSINT\Client\AuthKey\AuthKey;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\MTSerialization\OwnImplementation\OwnAnonymousMessage;
use TelegramOSINT\TGConnection\SocketMessenger\EncryptedSocketMessenger;
use TelegramOSINT\TGConnection\SocketMessenger\MessageListener;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;
use Throwable;

class TraceSocketMessenger extends EncryptedSocketMessenger
{
    /** @var float */
    private $timeOffset;

    /** @var array */
    private $trace;

    /** @var int[] */
    private $msgIds = [];

    /** @var TLClientMessage[] */
    private $msgs = [];

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
        if ($payload->getName() === 'update_status') {
            return;
        }
        $this->msgIds[] = $messageId;
        $this->msgs[] = $payload;
    }

    /** @noinspection SpellCheckingInspection */
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
            $unhexed = $orig = hex2bin($v[1]);
            $replaces = [
                //'TLMessage\\\\',
                'MTSerialization\\\\',
            ];
            foreach ($replaces as $repl) {
                $replacerGen = function ($prefix1) {
                    return function ($matches) use ($prefix1) {
                        $prefix = 'TelegramOSINT\\';
                        $matches[2] = (int) $matches[2];
                        $matches[2] += strlen($prefix);
                        $matches[3] = $prefix.$matches[3];

                        return "{$matches[1]}:{$matches[2]}:$prefix1{$matches[3]}";
                    };
                };
                $prefix3 = '"';
                $rx1 = '/(O):(\d+):'.$prefix3.'('.$repl.')/';
                $unhexed = preg_replace_callback($rx1, $replacerGen($prefix3), $unhexed);
                $prefix3 = '"'."\1\1\1";
                $rx2 = '/(s):(\d+):'.$prefix3.'('.$repl.')/';
                $search = '"'."\0";
                $unhexed = str_replace($search, $prefix3, $unhexed);
                $unhexed = preg_replace_callback($rx2, $replacerGen($prefix3), $unhexed);
                $unhexed = str_replace($prefix3, $search, $unhexed);

                try {
                    $msg = unserialize($unhexed);
                } catch (Throwable $e) {
                    /** @noinspection PhpUnhandledExceptionInspection */
                    throw $e;
                }
            }
            $arrMsg = (array) $msg;
            $arrMsg = reset($arrMsg);

            $reqMsgId = array_shift($this->msgIds);
            /** @noinspection PhpUnusedLocalVariableInspection */
            $reqMsg = array_shift($this->msgs);

            return new OwnAnonymousMessage([
                '_'          => 'rpc_result',
                'req_msg_id' => $reqMsgId,
                'result'     => $arrMsg,
            ]);
        }

        return null;
    }
}
