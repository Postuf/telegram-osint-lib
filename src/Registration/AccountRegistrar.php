<?php

declare(strict_types=1);

namespace TelegramOSINT\Registration;

use TelegramOSINT\Exception\TGException;
use TelegramOSINT\Logger\ClientDebugLogger;
use TelegramOSINT\Logger\DefaultLogger;
use TelegramOSINT\TGConnection\DataCentre;
use TelegramOSINT\Tools\Proxy;

class AccountRegistrar implements RegisterInterface
{
    /**
     * @var RegisterInterface
     */
    private $reg;

    /**
     * @param Proxy|null             $proxy
     * @param AccountInfo|null       $accountInfo
     * @param ClientDebugLogger|null $logger
     * @param DataCentre|null        $dataCentre
     */
    public function __construct(
        Proxy $proxy = null,
        AccountInfo $accountInfo = null,
        ClientDebugLogger $logger = null,
        ?DataCentre $dataCentre = null
    ) {
        $this->reg = new RegistrationFromTgApp(
            $proxy,
            $accountInfo,
            $logger ?: new DefaultLogger(),
            $dataCentre
        );
    }

    /**
     * @param int $timeoutSeconds
     */
    public function pollMessages(int $timeoutSeconds = 60): void
    {
        $this->reg->pollMessages();
    }

    /**
     * @param string   $phoneNumber
     * @param callable $cb          function(bool $reReg)
     * @param bool     $allowReReg
     *
     * @throws TGException
     */
    public function requestCodeForPhone(string $phoneNumber, callable $cb, bool $allowReReg = false): void
    {
        $phoneNumber = trim($phoneNumber);
        $this->reg->requestCodeForPhone($phoneNumber, $cb, $allowReReg);
    }

    /**
     * @param string   $smsCode
     * @param callable $onAuthKeyReady function(AuthKey $authKey)
     * @param bool     $reReg
     *
     * @throws TGException
     */
    public function confirmPhoneWithSmsCode(string $smsCode, callable $onAuthKeyReady, bool $reReg = false): void
    {
        $smsCode = trim($smsCode);
        $this->reg->confirmPhoneWithSmsCode($smsCode, $onAuthKeyReady, $reReg);
    }

    public function terminate(): void
    {
        $this->reg->terminate();
    }
}
