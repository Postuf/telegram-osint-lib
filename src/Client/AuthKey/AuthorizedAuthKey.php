<?php

namespace Client\AuthKey;

use Registration\AccountInfo;

interface AuthorizedAuthKey extends AuthKey
{

    /**
     * @return string
     */
    public function getPhone();


    /**
     * @return AccountInfo
     */
    public function getAccountInfo();


}