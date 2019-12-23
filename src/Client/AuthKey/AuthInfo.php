<?php

namespace Client\AuthKey;


use Registration\AccountInfo;


class AuthInfo
{

    /**
     * @var string
     */
    private $phone;
    /**
     * @var AccountInfo
     */
    private $account;


    /**
     * @param string $phone
     * @return AuthInfo
     */
    public function setPhone(string $phone)
    {
        $this->phone = $phone;
        return $this;
    }


    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }


    /**
     * @return AccountInfo
     */
    public function getAccount()
    {
        return $this->account;
    }


    /**
     * @param AccountInfo $account
     * @return AuthInfo
     */
    public function setAccountInfo(AccountInfo $account)
    {
        $this->account = $account;
        return $this;
    }


}