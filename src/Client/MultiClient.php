<?php

declare(strict_types=1);

namespace TelegramOSINT\Client;

use TelegramOSINT\Client\AuthKey\AuthKey;
use TelegramOSINT\Client\AuthKey\AuthKeyCreator;
use TelegramOSINT\Client\InfoObtainingClient\InfoClient;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\Logger\Logger;
use TelegramOSINT\Tools\Proxy;

class MultiClient
{
    /**
     * @var AuthKey[]
     */
    private $authKeys;

    /** @var InfoClient[] */
    private $clients;

    /** @var int */
    private $connectedCount = 0;

    /** @var float */
    private $startTime;

    /**
     * @param string[] $authKeysSerialized
     * @param callable $clientCreator      function(): InfoClient
     *
     * @throws TGException
     */
    public function __construct(array $authKeysSerialized, callable $clientCreator)
    {
        $this->clients = [];
        $this->authKeys = [];

        foreach ($authKeysSerialized as $keyStr) {
            $authKey = AuthKeyCreator::createFromString($keyStr);
            $this->authKeys[] = $authKey;
            $this->clients[] = $clientCreator();
        }
    }

    public function connect(?Proxy $proxy = null): void
    {
        //ini_set('xdebug.trace_format', 1);
        //xdebug_start_trace('/tmp/trace.xdebug');
        $this->startTime = microtime(true);
        $count = count($this->clients);
        foreach ($this->clients as $k => $client) {
            try {
                $authKey = $this->authKeys[$k];
                $client->login($authKey, $proxy, function () use ($authKey) {
                    $parts = explode(':', $authKey->getSerializedAuthKey());
                    $phone = $parts[0];
                    Logger::log(__CLASS__, $phone.' connected');
                    $this->connectedCount++;
                    if ($this->connectedCount == count($this->clients)) {
                        $timeDiff = microtime(true) - $this->startTime;
                        $timeDiffStr = number_format($timeDiff, 3);
                        Logger::log(__CLASS__, "all clients connected after $timeDiffStr sec");
                    }
                });
                $parts = explode(':', $authKey->getSerializedAuthKey());
                Logger::log(__CLASS__, "after login {$parts[0]}");
            } /** @noinspection PhpRedundantCatchClauseInspection */ catch (TGException $e) {
                Logger::log(__CLASS__, $e->getMessage());
            }
        }
        $timeDiff = microtime(true) - $this->startTime;
        $timeDiffStr = number_format($timeDiff, 3);
        Logger::log(__CLASS__, "login took: $timeDiffStr sec for $count clients");
        //xdebug_stop_trace();
    }

    /**
     * @throws TGException
     */
    public function poll(): void
    {
        if (!$this->clients) {
            throw new TGException(0, 'no clients');
        }
        foreach ($this->clients as $client) {
            $client->pollMessage();
        }
    }
}
