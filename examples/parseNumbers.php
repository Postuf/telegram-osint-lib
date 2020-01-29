<?php

declare(strict_types=1);

use TelegramOSINT\Client\InfoObtainingClient\Models\UserInfoModel;
use TelegramOSINT\Scenario\UserContactsScenario;

require_once __DIR__.'/../vendor/autoload.php';

// here we get contact list and get contact online status
// avatars are saved to current directory

if (!isset($argv[1])) {
    echo "please specify numbers (comma-separated): 79061231231,79061231232\n";
    exit(1);
}

$numbers = explode(',', $argv[1]);

$client = new UserContactsScenario(array_slice($numbers, 0, 1));
/* @noinspection PhpUnhandledExceptionInspection */

echo "Phone\t|\tUsername\t|\tFirst name\t|\tLast name\t|\tPhoto\t|\tAbout\t|\tCommon chats\t|\tLang\t|\tWas online\n\n";
$client->parseNumbers($numbers, true, true, function (UserInfoModel $model) {
        $photo_file = '';
        if ($model->photo){
            $photo_file = $model->phone.'.'.$model->photo->format;
            file_put_contents(
                $photo_file,
                $model->photo->bytes
            );
        }
        echo $model->phone."\t|\t".
            $model->username."\t|\t".
            $model->firstName."\t|\t".
            $model->lastName."\t|\t".
            $photo_file."\t|\t".
            $model->bio."\t|\t".
            $model->commonChatsCount."\t|\t".
            $model->langCode."\t|\t";
        if ($model->status->was_online)
            echo date('Y-m-d H:i:s', $model->status->was_online)."\n";
        elseif ($model->status->is_hidden)
            echo "Hidden\n";
        elseif ($model->status->is_online)
            echo "Online\n";
        else
            echo "\n";
});
/* @noinspection PhpUnhandledExceptionInspection */
$client->startActions();
