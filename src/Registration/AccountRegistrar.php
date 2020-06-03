<?php

declare(strict_types=1);

namespace TelegramOSINT\Registration;

use TelegramOSINT\Exception\TGException;
use TelegramOSINT\Logger\ClientDebugLogger;
use TelegramOSINT\Logger\DefaultLogger;
use TelegramOSINT\Tools\Proxy;

class AccountRegistrar implements RegisterInterface
{
    /**
     * @var RegisterInterface
     */
    private $reg;

    /**
     * @param Proxy|null $proxy
     * @param AccountInfo|null $accountInfo
     * @param ClientDebugLogger|null $logger
     */
    public function __construct(Proxy $proxy = null, AccountInfo $accountInfo = null, ClientDebugLogger $logger = null)
    {
        $this->reg = new RegistrationFromTgApp($proxy, $accountInfo, $logger ?: new DefaultLogger());
    }

    public function pollMessages() {
        $this->reg->pollMessages();
    }

    /**
     * @param string   $phoneNumber
     * @param callable $cb          function()
     *
     * @throws TGException
     */
    public function requestCodeForPhone(string $phoneNumber, callable $cb): void
    {
        $phoneNumber = trim($phoneNumber);
        $this->reg->requestCodeForPhone($phoneNumber, $cb);
    }

    /**
     * @param string   $smsCode
     * @param callable $onAuthKeyReady function(AuthKey $authKey)
     *
     * @throws TGException
     */
    public function confirmPhoneWithSmsCode(string $smsCode, callable $onAuthKeyReady): void
    {
        $smsCode = trim($smsCode);
        $this->reg->confirmPhoneWithSmsCode($smsCode, $onAuthKeyReady);
    }
}
