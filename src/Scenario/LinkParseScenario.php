<?php

declare(strict_types=1);

namespace TelegramOSINT\Scenario;

use TelegramOSINT\Exception\TGException;

class LinkParseScenario extends GroupMessagesScenario
{
    /**
     * @param bool $pollAndTerminate
     *
     * @throws TGException
     */
    public function startActions(bool $pollAndTerminate = true): void
    {
        $this->authAndPerformActions(function (): void {
            usleep(10000);
            $limit = 100;

            $parseLinksCallback = function () use ($limit) {
                $this->parseLinks($this->groupIdObj->getId(), $this->groupIdObj->getAccessHash(), $limit);
            };

            if ($this->username) {
                $this->infoClient->resolveUsername($this->username, $this->getUserResolveHandler($parseLinksCallback));
            } else {
                $parseLinksCallback();
            }
        }, $pollAndTerminate);
    }

    private function parseLinks(int $id, int $accessHash, int $limit): void
    {
        $this->infoClient->getChannelLinks($id, $limit, $accessHash, null, null, $this->makeMessagesHandler($id, $accessHash, $limit));
    }
}
