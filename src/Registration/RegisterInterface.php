<?php
declare(strict_types=1);

namespace Registration;

use Client\AuthKey\AuthKey;

interface RegisterInterface
{

    /**
     * @var $phoneNumber string
     */
    public function requestCodeForPhone(string $phoneNumber): void;

    /**
     * @var $smsCode string
     * @return AuthKey
     */
    public function confirmPhoneWithSmsCode(string $smsCode): AuthKey;

}