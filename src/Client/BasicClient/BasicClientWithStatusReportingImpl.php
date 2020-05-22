<?php

declare(strict_types=1);

namespace TelegramOSINT\Client\BasicClient;

use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\update_status;

class BasicClientWithStatusReportingImpl extends BasicClientImpl
{
    private const ONLINE_STATUS_UPDATE_TIME_SEC = 4 * 60 - 10;
    /**
     * @var int
     */
    private $lastStatusOnlineSet;

    protected function prePollMessage(): ?AnonymousMessage
    {
        $readMessage = parent::prePollMessage();
        $this->setOnlineStatusIfExpired();

        return $readMessage;
    }

    private function setOnlineStatusIfExpired(): void
    {
        $elapsedTimeSinceLastUpdate = time() - $this->lastStatusOnlineSet;
        if($elapsedTimeSinceLastUpdate >= self::ONLINE_STATUS_UPDATE_TIME_SEC){
            $this->getConnection()->writeMessage(new update_status(true));
            $this->lastStatusOnlineSet = time();
        }
    }

    public function terminate()
    {
        if($this->getConnection()) {
            $this->getConnection()->writeMessage(new update_status(false));
        }
        parent::terminate();
    }
}
