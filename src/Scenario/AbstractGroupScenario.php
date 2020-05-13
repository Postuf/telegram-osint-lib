<?php

declare(strict_types=1);

namespace TelegramOSINT\Scenario;

use TelegramOSINT\Logger\Logger;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Contact\ResolvedPeer;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Peer\PeerChannel;
use TelegramOSINT\Tools\Proxy;

abstract class AbstractGroupScenario extends InfoClientScenario implements ScenarioInterface
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

    public function __construct(?ClientGeneratorInterface $generator = null, ?Proxy $proxy = null)
    {
        parent::__construct($generator, $proxy);
    }

    /**
     * @param callable $onChannelFound function(AnonymousMessage $message)
     *
     * @return callable function(AnonymousMessage $message)
     */
    protected function getResolveHandler(callable $onChannelFound): callable
    {
        return function (AnonymousMessage $message) use ($onChannelFound) {
            if (!ResolvedPeer::isIt($message)) {
                Logger::log(__CLASS__, 'got unexpected response of type '.$message->getType());

                return;
            }
            /** @var array $peer */
            $peer = (new ResolvedPeer($message))->getPeer();
            if (!($peer instanceof PeerChannel)) {
                Logger::log(__CLASS__, 'got unexpected peer type');

                return;
            }

            $onChannelFound($message);
        };
    }
}
