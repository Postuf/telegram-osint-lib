<?php

declare(strict_types=1);

namespace Helpers;

use Client\BasicClient\BasicClientImpl;
use SocksProxyAsync\Proxy;
use TGConnection\DataCentre;
use TGConnection\Socket\Socket;
use TGConnection\SocketMessenger\SocketMessenger;

class NullBasicClientImpl extends BasicClientImpl
{
    /** @var array */
    private $traceArray;

    public function __construct(array $traceArray)
    {
        parent::__construct();
        $this->traceArray = $traceArray;
    }

    protected function pickSocket(DataCentre $dc, Proxy $proxy = null, callable $cb = null): Socket
    {
        return new NullSocket();
    }

    protected function getSocketMessenger(): SocketMessenger
    {
        return new TraceSocketMessenger($this->traceArray, $this->getAuthKey(), $this);
    }
}
