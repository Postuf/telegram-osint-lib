<?php

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__ . '/MyTgClientDebug.php';

// в этом примере мы получаем список контактов пользователя и онлайн статусы контактов
// аватары контактов сохраняются в текущую директорию

/** @noinspection PhpUnhandledExceptionInspection */
(new MyTgClientDebug())->startActions();
