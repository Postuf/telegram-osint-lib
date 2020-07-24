<?php

declare(strict_types=1);

namespace Helpers;

use InvalidArgumentException;
use TelegramOSINT\Client\AuthKey\AuthKey;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\MTSerialization\OwnImplementation\OwnAnonymousMessage;
use TelegramOSINT\TGConnection\SocketMessenger\EncryptedSocketMessenger;
use TelegramOSINT\TGConnection\SocketMessenger\MessageListener;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\ping_delay_disconnect;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

class TraceSocketMessenger extends EncryptedSocketMessenger
{
    /** @var array */
    private array $trace;

    /** @var int[] */
    private array $msgIds = [];

    /** @var TLClientMessage[] */
    private array $msgs = [];

    /**
     * @param array           $trace    see tests/Integration/Scenario for
     * @param AuthKey         $authKey
     * @param MessageListener $callback
     *
     * @throws TGException
     */
    public function __construct(array $trace, AuthKey $authKey, MessageListener $callback)
    {
        parent::__construct(new NullSocket(), $authKey, $callback);
        $this->trace = $trace;
    }

    /**
     * @param string $serialized
     *
     * @throws InvalidArgumentException
     *
     * @return AnonymousMessage
     */
    public static function unserializeAnonymousMessage(string $serialized): AnonymousMessage
    {
        /** @noinspection UnserializeExploitsInspection */
        $messageOrFalse = unserialize(PhpSerializationFixer::replaceNamespace(
            $serialized,
            'MTSerialization\\\\',
            'TelegramOSINT\\MTSerialization\\'
        ));
        if ($messageOrFalse === false) {
            throw new InvalidArgumentException('Cannot unserialize message');
        }
        if (!($messageOrFalse instanceof AnonymousMessage)) {
            throw new InvalidArgumentException('Input value is not `'.AnonymousMessage::class.'`.');
        }

        return $messageOrFalse;
    }

    protected function writeIdentifiedMessage(TLClientMessage $payload, $messageId): void
    {
        if ($payload->getName() === 'update_status') {
            return;
        }
        $this->msgIds[] = $messageId;
        $this->msgs[] = $payload;
    }

    protected function readMessageFromSocket(): ?AnonymousMessage
    {
        if (!$this->trace[1]) {
            return null;
        }

        /** @noinspection LoopWhichDoesNotLoopInspection */
        foreach ($this->trace[1] as $k => $v) {
            $msg = static::unserializeAnonymousMessage(hex2bin($v[1]));
            $arrMsg = (array) $msg;
            $arrMsg = reset($arrMsg);

            $reqMsgId = array_shift($this->msgIds);
            $reqMsg = array_shift($this->msgs);

            $retain = true;
            if ($reqMsg && $reqMsg instanceof ping_delay_disconnect) {
                $arrMsg['_'] = 'pong';
                $retain = false;
            }

            if ($reqMsg) {
                if ($retain) {
                    unset($this->trace[1][$k]);
                }

                return new OwnAnonymousMessage([
                    '_'          => 'rpc_result',
                    'req_msg_id' => $reqMsgId,
                    'result'     => $arrMsg,
                ]);
            }

            return null;
        }

        return null;
    }
}
