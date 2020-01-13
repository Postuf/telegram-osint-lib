<?php

use Scenario\MyTgClientDebug;

require_once __DIR__.'/../vendor/autoload.php';

// here we get contact list and get contact online status
// avatars are saved to current directory

/* @noinspection PhpUnhandledExceptionInspection */
(new MyTgClientDebug())->startActions();
