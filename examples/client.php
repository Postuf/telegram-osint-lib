<?php

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__ . '/MyTgClientDebug.php';

// here we get contact list and get contact online status
// avatars are saved to current directory

/** @noinspection PhpUnhandledExceptionInspection */
(new MyTgClientDebug())->startActions();
