<?php
declare(strict_types=1);

namespace Registration;

interface RegisterInterface
{

    /**
     *
     * @param string $phoneNumber
     * @param callable $cb
     */
    public function requestCodeForPhone(string $phoneNumber, callable $cb): void;

    /**
     * @param string $smsCode
     * @param callable $cb function(AuthKey $authKey)
     */
    public function confirmPhoneWithSmsCode(string $smsCode, callable $cb): void;

    public function pollMessages();
}