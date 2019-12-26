<?php

use Client\AuthKey\AuthKey;
use Client\AuthKey\AuthKeyCreator;
use Client\InfoObtainingClient\InfoClient;
use Exception\TGException;
use Logger\Logger;

class MultiClient
{
    /**
     * @var AuthKey[]
     */
    private $authKeys;

    /** @var InfoClient[] */
    private $clients;

    /**
     * @param string[] $authKeysSerialized
     *
     * @throws TGException
     */
    public function __construct(array $authKeysSerialized)
    {
        $this->clients = [];
        $this->authKeys = [];

        foreach ($authKeysSerialized as $keyStr) {
            $authKey = AuthKeyCreator::createFromString($keyStr);
            $this->authKeys[] = $authKey;
            $this->clients[] = new InfoClient();
        }
    }

    public function connect(): void
    {
        $timeStart = time();
        $count = count($this->clients);
        foreach ($this->clients as $k => $client) {
            try {
                $client->login($this->authKeys[$k]);
            } catch (TGException $e) {
                Logger::log(__CLASS__, $e->getMessage());
            }
        }
        $timeDiff = time() - $timeStart;
        Logger::log(__CLASS__, "Login took: $timeDiff sec for $count clients");
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
