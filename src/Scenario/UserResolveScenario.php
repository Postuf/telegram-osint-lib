<?php

declare(strict_types=1);

namespace TelegramOSINT\Scenario;

use TelegramOSINT\Exception\TGException;
use TelegramOSINT\Logger\Logger;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Contact\ResolvedPeer;

class UserResolveScenario extends InfoClientScenario
{
    /** @var string */
    private $username;
    /** @var int|null */
    private $userId;
    /** @var callable */
    private $cb;
    /** @var bool */
    private $standalone;

    /**
     * @param string                        $username
     * @param callable                      $cb              function(int|null $userId, int|null $accessHash)
     * @param bool                          $standalone
     * @param ClientGeneratorInterface|null $clientGenerator
     *
     * @throws TGException
     */
    public function __construct(string $username, callable $cb, bool $standalone = true, ClientGeneratorInterface $clientGenerator = null)
    {
        parent::__construct($clientGenerator);
        $this->username = $username;
        $this->cb = $cb;
        $this->standalone = $standalone;
    }

    /**
     * @param bool $pollAndTerminate
     *
     * @throws TGException
     */
    public function startActions(bool $pollAndTerminate = true): void
    {
        $action = function () use ($pollAndTerminate) {
            $this->infoClient->resolveUsername($this->username, $this->getUserResolveHandler($this->cb));

            if ($pollAndTerminate) {
                $this->pollAndTerminate();
            }
        };
        if ($this->standalone) {
            $this->authAndPerformActions($action, $pollAndTerminate);
        } else {
            $action();
        }
    }

    /**
     * @param callable $cb function()
     *
     * @return callable function(AnonymousMessage $msg)
     */
    private function getUserResolveHandler(callable $cb): callable
    {
        return function (AnonymousMessage $message) use ($cb) {
            if (ResolvedPeer::isIt($message)
                && ($peer = new ResolvedPeer($message))
                && $peer->getUsers()) {
                $user = $peer->getUsers()[0];
                $this->userId = $user->id;
                Logger::log(__CLASS__, "resolved user {$this->username} to {$this->userId}");
                $cb($this->userId, $user->accessHash);

                return;
            }
            $cb(null, null);
        };
    }
}
