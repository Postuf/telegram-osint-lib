<?php


use Client\AuthKey\AuthKey;
use Client\AuthKey\AuthKeyCreator;
use Client\InfoObtainingClient\InfoClient;
use Exception\TGException;

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
     * @throws TGException
     */
    public function __construct(array $authKeysSerialized)
    {
        foreach ($authKeysSerialized as $keyStr) {
            $authKey = AuthKeyCreator::createFromString($keyStr);
            $this->authKeys[] = $authKey;
            $this->clients[] = new InfoClient();
        }
    }

    public function connect(): void
    {
        foreach ($this->clients as $k => $client) {
            $client->login($this->authKeys[$k]);
        }
    }
}