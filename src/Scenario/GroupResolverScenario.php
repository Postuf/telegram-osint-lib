<?php

declare(strict_types=1);

namespace TelegramOSINT\Scenario;

use LogicException;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\Scenario\Models\GroupRequest;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Contact\ResolvedPeer;

class GroupResolverScenario extends InfoClientScenario
{
    /** @var GroupRequest */
    private $groupRequest;
    /** @var int|null */
    private $groupId;
    /** @var callable|null */
    private $onReady;
    /** @var ClientGeneratorInterface */
    private $generator;

    /**
     * @param GroupRequest             $request
     * @param ClientGeneratorInterface $generator
     * @param callable                 $onReady   function(?int $groupId, ?int $accessHash)
     *
     * @throws TGException
     */
    public function __construct(GroupRequest $request, ClientGeneratorInterface $generator, callable $onReady)
    {
        parent::__construct($generator);
        $this->groupRequest = $request;
        if ($this->groupRequest->getGroupId()) {
            $this->groupId = $this->groupRequest->getGroupId();
        }
        $this->onReady = $onReady;
        $this->generator = $generator;
    }

    /**
     * @throws TGException
     *
     * @return bool true if ready
     */
    public function poll(): bool
    {
        $this->pollAndTerminate(0.0, false);

        return $this->onReady === null;
    }

    /** @noinspection DuplicatedCode */
    private function getGroupResolveHandler(): callable
    {
        return function (AnonymousMessage $message) {
            $onReady = $this->onReady;
            if (!$onReady) {
                return;
            }
            if (ResolvedPeer::isIt($message)
                && ($resolvedPeer = new ResolvedPeer($message))
                && ($chats = $resolvedPeer->getChats())) {
                foreach ($chats as $chat) {
                    $id = (int) $chat->id;
                    $accessHash = (int) $chat->accessHash;
                    $onReady($id, $accessHash);
                    $this->onReady = null;
                    break;
                }
            } else {
                $onReady(null);
                $this->onReady = null;
            }
        };
    }

    /**
     * @param bool $pollAndTerminate
     *
     * @throws TGException
     */
    public function startActions(bool $pollAndTerminate = true): void
    {
        $this->authAndPerformActions(function (): void {
            if ($this->groupRequest->getUserName()) {
                $this->infoClient->resolveUsername($this->groupRequest->getUserName(), $this->getGroupResolveHandler());
            } else {
                // TODO
                throw new LogicException('Not implemented');
            }
        }, $pollAndTerminate);
    }
}
