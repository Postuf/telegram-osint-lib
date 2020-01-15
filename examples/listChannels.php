<?php

declare(strict_types=1);

use TelegramOSINT\Scenario\GroupPhotosScenario;

require_once __DIR__.'/../vendor/autoload.php';

/* @noinspection PhpUnhandledExceptionInspection */
(new GroupPhotosScenario())->listChats('channel');
