<?php

declare(strict_types=1);

namespace TelegramOSINT\Client;

use TelegramOSINT\Client\InfoObtainingClient\Models\GroupId;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Contact\ContactUser;

interface UserInfoClient
{
    /**
     * @param string   $username
     * @param callable $onComplete function(AnonymousMessage $msg)
     */
    public function resolveUsername(string $username, callable $onComplete): void;

    /**
     * @param GroupId  $id
     * @param int      $msgId
     * @param int      $userId
     * @param callable $onComplete function(?UserInfoModel $model)
     * @noinspection PhpUnused
     * @noinspection UnknownInspectionInspection
     */
    public function getFullUser(GroupId $id, int $msgId, int $userId, callable $onComplete): void;

    /**
     * @param ContactUser $user
     * @param bool        $withPhoto
     * @param bool        $largePhoto
     * @param callable    $onComplete
     */
    public function getFullUserInfo(ContactUser $user, bool $withPhoto, bool $largePhoto, callable $onComplete): void;

    /**
     * @param float    $latitude
     * @param float    $longitude
     * @param callable $onComplete function(AnonymousMessage $msg)
     */
    public function getLocated(float $latitude, float $longitude, callable $onComplete): void;
}
