<?php

declare(strict_types=1);

use Scenario\GroupPhotosClient;

require_once __DIR__.'/../vendor/autoload.php';

/* @noinspection PhpUnhandledExceptionInspection */
(new GroupPhotosClient())->listChats('chat');
