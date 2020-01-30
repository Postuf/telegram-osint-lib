<?php

declare(strict_types=1);

use TelegramOSINT\Scenario\GroupPhotosScenario;
use TelegramOSINT\Scenario\Models\OptionalDateRange;

require_once __DIR__.'/../vendor/autoload.php';

/* @noinspection PhpUnhandledExceptionInspection */
(new GroupPhotosScenario(new OptionalDateRange()))->listChats('chat');
