<?php

namespace TelegramOSINT\Client;

use TelegramOSINT\Client\InfoObtainingClient\Models\FileModel;

interface InfoObtainingClient extends Client
{
    /**
     * @param string   $phone
     * @param bool     $withPhoto
     * @param bool     $largePhoto
     * @param callable $onComplete function(?UserInfoModel $model)
     */
    public function getInfoByPhone(string $phone, bool $withPhoto, bool $largePhoto, callable $onComplete): void;

    /**
     * @param string   $userName
     * @param bool     $withPhoto
     * @param bool     $largePhoto
     * @param callable $onComplete function(?UserInfoModel $model)
     */
    public function getInfoByUsername(string $userName, bool $withPhoto, bool $largePhoto, callable $onComplete): void;

    public function loadFile(FileModel $model, callable $onPictureLoaded);
}
