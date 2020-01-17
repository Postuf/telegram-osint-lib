<?php

declare(strict_types=1);

namespace TelegramOSINT\Scenario;

use TelegramOSINT\Client\InfoObtainingClient\InfoClient;

class ReusableClientGenerator extends ClientGenerator
{
    /** @var InfoClient */
    private $instance;
    /** @var ClientGeneratorInterface */
    private $clientGenerator;

    public function __construct(?ClientGeneratorInterface $clientGenerator = null)
    {
        $this->clientGenerator = $clientGenerator;
    }

    public function getAuthKeyInfo(): string
    {
        return $this->clientGenerator
            ? $this->clientGenerator->getAuthKeyInfo()
            : parent::getAuthKeyInfo();
    }

    public function getInfoClient()
    {
        if (!$this->instance) {
            $this->setInstance();
        }

        return $this->instance;
    }

    private function setInstance(): void
    {
        $this->instance = $this->clientGenerator
            ? $this->clientGenerator->getInfoClient()
            : parent::getInfoClient();
    }
}
