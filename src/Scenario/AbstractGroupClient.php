<?php

declare(strict_types=1);

namespace Scenario;

use Logger\Logger;
use MTSerialization\AnonymousMessage;
use SocksProxyAsync\Proxy;

abstract class AbstractGroupClient extends MyTgClientDebug implements ScenarioInterface
{
    /** @var int|null */
    protected $groupId;
    /** @var string|null */
    protected $deepLink;

    public function setGroupId(int $groupId): void
    {
        $this->groupId = $groupId;
    }

    public function setDeepLink(string $deepLink): void
    {
        $this->deepLink = $deepLink;
    }

    public function __construct(?Proxy $proxy = null, ?ClientGeneratorInterface $generator = null)
    {
        parent::__construct($proxy, $generator);
    }

    /**
     * @param callable $onChannelFound function(AnonymousMessage $message)
     *
     * @return callable function(AnonymousMessage $message)
     */
    protected function getResolveHandler(callable $onChannelFound): callable
    {
        return function (AnonymousMessage $message) use ($onChannelFound) {
            if ($message->getType() !== 'contacts.resolvedPeer') {
                Logger::log(__CLASS__, 'got unexpected response of type '.$message->getType());

                return;
            }
            /** @var array $peer */
            $peer = $message->getValue('peer');
            if ($peer['_'] !== 'peerChannel') {
                Logger::log(__CLASS__, 'got unexpected peer of type '.$peer['_']);

                return;
            }

            $onChannelFound($message);
        };
    }
}
