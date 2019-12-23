<?php

namespace Client\InfoObtainingClient\Models;


class UserStatusModel
{

    /**
     * @var boolean
     */
    public $is_online;
    /**
     * @var boolean
     */
    public $is_hidden;
    /**
     * @var int
     */
    public $was_online;
    /**
     * @var int
     */
    public $expires;

}