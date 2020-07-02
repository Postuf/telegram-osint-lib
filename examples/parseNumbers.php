<?php

declare(strict_types=1);

use TelegramOSINT\Client\InfoObtainingClient\Models\UserInfoModel;
use TelegramOSINT\Scenario\UserContactsScenario;

require_once __DIR__.'/../vendor/autoload.php';

// here we get contact list and get contact online status
// avatars are saved to current directory

$argsOrFalse = getopt('n:u:h', ['numbers:', 'users:', 'help']);
if ($argsOrFalse === false
    || (array_key_exists('h', $argsOrFalse) || array_key_exists('help', $argsOrFalse))
    || ((!array_key_exists('n', $argsOrFalse) && !array_key_exists('numbers', $argsOrFalse))
        && (!array_key_exists('u', $argsOrFalse) && !array_key_exists('users', $argsOrFalse)))
) {
    echo <<<'EOT'
Usage:
    php parseNumbers.php -n numbers [ -u users ]
    php parseNumbers.php --numbers numbers
    php parseNumbers.php --users users

   -n, --numbers                Comma separated phone number list (e.g. 79061231231,79061231232).
   -u, --users                  Comma separated username list (e.g. aaa,bbb).
   -h, --help                   Display this help message.

EOT;
    exit(1);
}

$numbers = array_filter(explode(',', $argsOrFalse['n'] ?? $argsOrFalse['numbers'] ?? ''));
$users = array_filter(explode(',', $argsOrFalse['u'] ?? $argsOrFalse['users'] ?? ''));

$onComplete = static function (UserInfoModel $model) {
    $photo_file = '';
    if ($model->photo){
        $photo_file = $model->phone.'.'.$model->photo->format;
        file_put_contents(
            $photo_file,
            $model->photo->bytes
        );
    }
    echo implode("\t|\t", [
        $model->phone,
        $model->username,
        $model->firstName,
        $model->lastName,
        $photo_file,
        $model->bio,
        $model->commonChatsCount,
        $model->langCode,
        '',
    ]);

    if ($model->status->was_online) {
        echo date('Y-m-d H:i:s', $model->status->was_online)."\n";
    }
    elseif ($model->status->is_hidden) {
        echo "Hidden\n";
    }
    elseif ($model->status->is_online) {
        echo "Online\n";
    }
    else {
        echo "\n";
    }
};

echo "Phone\t|\tUsername\t|\tFirst name\t|\tLast name\t|\tPhoto\t|\tAbout\t|\tCommon chats\t|\tLang\t|\tWas online\n\n";
/** @noinspection PhpUnhandledExceptionInspection */
$client = new UserContactsScenario(
    $numbers,
    $users,
    $onComplete,
    null,
    false,
    false
);
/* @noinspection PhpUnhandledExceptionInspection */
$client->startActions();
