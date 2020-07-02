<?php

declare(strict_types=1);

namespace TelegramOSINT\Registration;

interface RegisterInterface
{
    /**
     * @param string   $phoneNumber
     * @param callable $cb          function()
     * @param bool     $allowReReg
     */
    public function requestCodeForPhone(string $phoneNumber, callable $cb, bool $allowReReg = false): void;

    /**
     * @param string   $smsCode
     * @param callable $onAuthKeyReady function(AuthKey $authKey)
     * @param bool     $reReg
     */
    public function confirmPhoneWithSmsCode(string $smsCode, callable $onAuthKeyReady, bool $reReg = false): void;

    public function pollMessages(): void;

    public function terminate(): void;
}
