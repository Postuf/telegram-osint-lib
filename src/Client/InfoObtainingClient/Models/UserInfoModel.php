<?php

namespace Client\InfoObtainingClient\Models;

class UserInfoModel
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var int
     */
    public $accessHash;
    /**
     * @var string
     */
    public $username;
    /**
     * @var string
     */
    public $bio;
    /**
     * @var string
     */
    public $phone;
    /**
     * @var PictureModel
     */
    public $photo;
    /**
     * @var UserStatusModel
     */
    public $status;

}
