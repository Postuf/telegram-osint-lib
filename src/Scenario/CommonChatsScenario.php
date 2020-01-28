<?php

declare(strict_types=1);

namespace TelegramOSINT\Scenario;

use TelegramOSINT\Client\AuthKey\AuthKeyCreator;
use TelegramOSINT\Client\InfoObtainingClient\InfoClient;
use TelegramOSINT\Client\InfoObtainingClient\Models\UserInfoModel;
use TelegramOSINT\Logger\Logger;
use TelegramOSINT\Scenario\ScenarioInterface;
use TelegramOSINT\Logger\ClientDebugLogger;
use TelegramOSINT\Tools\Proxy;

class CommonChatsScenario implements ClientDebugLogger, ScenarioInterface
{
    /** @var InfoClient */
    protected $infoClient;
    /** @var string */
    private $authKeyForFirstClient;
    /** @var string */
    private $authKeyForSecondClient;
    /** @var Proxy */
    private $proxy;
    /** @var float */
    private $timeout = 5.0;

    public function __construct(?Proxy $proxy = null, ?ClientGeneratorInterface $generator = null)
    {
        /**
         * Set TL-node logger
         */
        Logger::setupLogger($this);

        if (!$generator) {
            $generator  = new ClientGenerator();
        }

        $this->authKeyForFirstClient = $generator->getAuthKeyInfo();

        $this->infoClient = $generator->getInfoClient();
        $this->proxy = $proxy;
    }

    public function login(): void
    {
        $authKey = AuthKeyCreator::createFromString($this->authKeyForFirstClient);
        if (!$this->infoClient->isLoggedIn()) {
            $this->infoClient->login($authKey, $this->proxy);
        }
    }

    public function startActions(bool $pollAndTerminate = true): void
    {
        if ($pollAndTerminate) {
            $this->pollAndTerminate();
        }
    }

    public function getCommonChats(string $phone, array $groups, ?callable $callback = null)
    {
        $limit = 10;

        foreach ($groups as $group) {

        }

        $this->infoClient->getInfoByPhone($phone, false, false, function(?UserInfoModel $userModel) use ($limit, $callback){
            if ($userModel) {

                $this->infoClient->getCommonChats($userModel->id, $userModel->accessHash, $limit, 0, $callback);
            }
        });
    }

    public function debugLibLog(string $dbgLabel, string $dbgMessage)
    {
        echo date('d.m.Y H:i:s').' | '.$dbgLabel.': '.$dbgMessage."\n";
    }

    public function pollAndTerminate(float $timeout = 0.0): void
    {
        if ($timeout == 0.0) {
            $timeout = $this->timeout;
        }
        $lastMsg = microtime(true);
        while(true) {
            if ($this->infoClient->pollMessage()) {
                $lastMsg = microtime(true);
            }
            if (microtime(true) - $lastMsg > $timeout)
                break;
            usleep(10000);
        }

        $this->infoClient->terminate();
    }
}
