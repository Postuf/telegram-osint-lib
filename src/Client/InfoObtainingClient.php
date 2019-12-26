<?php

namespace Client;

interface InfoObtainingClient extends Client
{
    /**
     * @param string   $phone
     * @param bool     $withPhoto
     * @param bool     $largePhoto
     * @param callable $onComplete
     */
    public function getInfoByPhone(string $phone, bool $withPhoto, bool $largePhoto, callable $onComplete);

    /**
     * @param string   $userName
     * @param bool     $withPhoto
     * @param bool     $largePhoto
     * @param callable $onComplete
     */
    public function getInfoByUsername(string $userName, bool $withPhoto, bool $largePhoto, callable $onComplete);
}
