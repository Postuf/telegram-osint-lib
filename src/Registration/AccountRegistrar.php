<?php
declare(strict_types=1);

namespace Registration;


use Exception\TGException;
use SocksProxyAsync\Proxy;

class AccountRegistrar implements RegisterInterface
{

    /**
     * @var RegisterInterface
     */
    private $reg;


    /**
     * @param AccountInfo|null $accountInfo
     * @param Proxy|null $proxy
     */
    public function __construct(Proxy $proxy = null, AccountInfo $accountInfo = null)
    {
        $this->reg = new RegistrationFromTgApp($proxy, $accountInfo);
    }

    public function pollMessages() {
        $this->reg->pollMessages();
    }

    /**
     *
     * @param string $phoneNumber
     * @param callable $cb
     * @throws TGException
     */
    public function requestCodeForPhone(string $phoneNumber, callable $cb): void
    {
        $phoneNumber = trim($phoneNumber);
        $this->reg->requestCodeForPhone($phoneNumber, $cb);
    }


    /**
     * @param string $smsCode
     * @param callable $cb
     *
     * @throws TGException
     */
    public function confirmPhoneWithSmsCode(string $smsCode, callable $cb): void
    {
        $smsCode = trim($smsCode);
        $this->reg->confirmPhoneWithSmsCode($smsCode, $cb);
    }

}