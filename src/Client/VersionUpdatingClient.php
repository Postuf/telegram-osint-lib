<?php

declare(strict_types=1);

namespace TelegramOSINT\Client;

use TelegramOSINT\Client\AuthKey\AuthKey;
use TelegramOSINT\Client\BasicClient\BasicClient;
use TelegramOSINT\LibConfig;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\Registration\AccountInfo;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\Shared\get_config;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\TgApp\init_connection;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\TgApp\invoke_with_layer;
use TelegramOSINT\Tools\Proxy;

abstract class VersionUpdatingClient
{
    /**
     * @var BasicClient
     */
    protected $basicClient;

    public function __construct(BasicClient $basicClient)
    {
        $this->basicClient = $basicClient;
    }

    /**
     * @param AuthKey       $authKey
     * @param Proxy         $proxy
     * @param callable|null $cb      function()
     *
     * @return void
     */
    public function login(AuthKey $authKey, Proxy $proxy = null, ?callable $cb = null)
    {
        $this->basicClient->login($authKey, $proxy, $cb ? function () use ($cb) {
            $this->bumpProtocolVersion();
            $cb();
        } : null);
        if (!$cb) {
            $this->bumpProtocolVersion();
        }
    }

    private function bumpProtocolVersion(): void
    {
        $initConnection = new init_connection(AccountInfo::generate(), new get_config());
        $requestWithLayer = new invoke_with_layer(LibConfig::APP_DEFAULT_TL_LAYER_VERSION, $initConnection);
        $this->basicClient->getConnection()->getResponseAsync($requestWithLayer, function (AnonymousMessage $response) {
        });
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->basicClient->isLoggedIn();
    }
}
