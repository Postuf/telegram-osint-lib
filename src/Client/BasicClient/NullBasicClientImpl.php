<?php

declare(strict_types=1);

namespace Client\BasicClient;

use Client\AuthKey\AuthKey;
use SocksProxyAsync\Proxy;
use TGConnection\DataCentre;
use TGConnection\Socket\NullSocket;
use TGConnection\SocketMessenger\TraceSocketMessenger;

class NullBasicClientImpl extends BasicClientImpl
{
    /** @var array */
    private $traceArray;

    public function __construct(array $traceArray)
    {
        parent::__construct(false);
        $this->traceArray = $traceArray;
    }

    protected function pickSocket(DataCentre $dc, Proxy $proxy = null, callable $cb = null)
    {
        return new NullSocket();
    }

    public function login(AuthKey $authKey, ?Proxy $proxy = null, callable $cb = null)
    {
        $dc = $authKey->getAttachedDC();
        $this->socket = $this->pickSocket($dc, $proxy, $cb);

        $this->connection = new TraceSocketMessenger($this->traceArray, $authKey, $this);
        $this->authKey = $authKey;
        $this->isLoggedIn = true;
    }
}
