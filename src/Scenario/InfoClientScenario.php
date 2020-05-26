<?php

declare(strict_types=1);

namespace TelegramOSINT\Scenario;

use function call_user_func;
use function microtime;
use TelegramOSINT\Client\AuthKey\AuthKeyCreator;
use TelegramOSINT\Client\InfoObtainingClient\InfoClient;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\Tools\Proxy;
use function usleep;

abstract class InfoClientScenario implements ScenarioInterface
{
    /** @var InfoClient */
    protected $infoClient;
    /** @var float */
    private $timeout = 3.0;
    /** @var string */
    private $authKey;
    /** @var ClientGeneratorInterface */
    private $generator;
    /** @var Proxy|null */
    private $proxy;

    /**
     * @param ClientGeneratorInterface|null $clientGenerator
     * @param Proxy|null                    $proxy
     *
     * @throws TGException
     */
    public function __construct(ClientGeneratorInterface $clientGenerator = null, ?Proxy $proxy = null)
    {
        if (!$clientGenerator) {
            $clientGenerator = new ClientGenerator();
        }
        $this->generator = $clientGenerator;
        $this->infoClient = $clientGenerator->getInfoClient();
        $this->authKey = $clientGenerator->getAuthKey();
        $this->proxy = $proxy ?: $clientGenerator->getProxy();
    }

    public function setTimeout(float $timeout): void
    {
        $this->timeout = $timeout;
    }

    /**
     * @throws TGException
     */
    protected function login(): void
    {
        $authKey = AuthKeyCreator::createFromString($this->authKey);
        if (!$this->infoClient->isLoggedIn()) {
            $this->infoClient->login($authKey, $this->proxy);
        }
    }

    /**
     * Fetch messages and terminate client
     *
     * @param float $timeout   seconds
     * @param bool  $terminate
     *
     * @throws TGException
     * @noinspection DuplicatedCode
     */
    protected function pollAndTerminate(float $timeout = 0.0, bool $terminate = true): void
    {
        if ($timeout == 0.0) {
            $timeout = $this->timeout;
        }
        $lastMsg = microtime(true);
        while (true) {

            if ($this->infoClient->pollMessage()) {
                $lastMsg = microtime(true);
            }

            if (microtime(true) - $lastMsg > $timeout)
                break;

            usleep(10000);
        }

        if ($terminate) {
            $this->infoClient->terminate();
        }
    }

    /**
     * Wraps authentication/login and pollAndTerminate with a given actions callable.
     *
     * @param callable $actions
     * @param bool     $pollAndTerminate
     * @param float    $timeout
     * @param bool     $terminate
     *
     * @throws TGException
     *
     * @return void
     */
    protected function authAndPerformActions(
        callable $actions,
        bool $pollAndTerminate = true,
        float $timeout = 0,
        bool $terminate = true
    ): void {
        $this->login();

        call_user_func($actions);

        if ($pollAndTerminate) {
            $this->pollAndTerminate($timeout, $terminate);
        }
    }

    protected function getGenerator(): ClientGeneratorInterface
    {
        return $this->generator;
    }
}
