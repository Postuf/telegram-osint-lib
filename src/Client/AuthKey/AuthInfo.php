<?php

namespace TelegramOSINT\Client\AuthKey;

use TelegramOSINT\Registration\AccountInfo;

class AuthInfo
{
    private string $phone;
    private AccountInfo $account;

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getAccount(): AccountInfo
    {
        return $this->account;
    }

    /**
     * @param AccountInfo $account
     *
     * @return AuthInfo
     */
    public function setAccountInfo(AccountInfo $account): self
    {
        $this->account = $account;

        return $this;
    }
}
