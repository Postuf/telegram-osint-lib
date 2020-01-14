<?php

declare(strict_types=1);

use Scenario\GroupPhotosScenario;

require_once __DIR__.'/../vendor/autoload.php';

/* @noinspection PhpUnhandledExceptionInspection */
(new GroupPhotosScenario())->listChats('chat');
