<?php
declare(strict_types=1);

namespace Registration;


use Client\AuthKey\AuthKey;
use Exception\TGException;
use Tools\Proxy;

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


    /**
     * @var $phoneNumber string
     * @throws TGException
     */
    public function requestCodeForPhone(string $phoneNumber): void
    {
        $phoneNumber = trim($phoneNumber);
        $this->reg->requestCodeForPhone($phoneNumber);
    }


    /**
     * @param string $smsCode
     * @return AuthKey
     *
     * @throws TGException
     */
    public function confirmPhoneWithSmsCode(string $smsCode): AuthKey
    {
        $smsCode = trim($smsCode);
        return $this->reg->confirmPhoneWithSmsCode($smsCode);
    }

}