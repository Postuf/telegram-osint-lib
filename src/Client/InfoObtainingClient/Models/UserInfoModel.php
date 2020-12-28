<?php

namespace TelegramOSINT\Client\InfoObtainingClient\Models;

class UserInfoModel
{
    public int $id;
    // whether model was or was not built due to network limits
    public bool $retry = false;
    public int $accessHash;
    public ?string $username = null;
    public ?string $bio = null;
    public ?string $phone = null;
    public ?PictureModel $photo = null;
    public ?UserStatusModel $status = null;
    public int $commonChatsCount = 0;
    public ?string $firstName = null;
    public ?string $lastName = null;
    public ?string $langCode = null;
}
