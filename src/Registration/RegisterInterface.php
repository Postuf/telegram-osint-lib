<?php

declare(strict_types=1);

namespace TelegramOSINT\Registration;

interface RegisterInterface
{
    /**
     * @param string   $phoneNumber
     * @param callable $cb          function()
     */
    public function requestCodeForPhone(string $phoneNumber, callable $cb): void;

    /**
     * @param string   $smsCode
     * @param callable $onAuthKeyReady function(AuthKey $authKey)
     */
    public function confirmPhoneWithSmsCode(string $smsCode, callable $onAuthKeyReady): void;

    public function pollMessages();
}
