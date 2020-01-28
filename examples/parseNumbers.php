<?php

declare(strict_types=1);

use TelegramOSINT\Client\InfoObtainingClient\Models\UserInfoModel;
use TelegramOSINT\Scenario\MyTgClientDebug;

require_once __DIR__.'/../vendor/autoload.php';

// here we get contact list and get contact online status
// avatars are saved to current directory

if (!isset($argv[1])) {
    echo "please specify numbers (comma-separated): 79061231231,79061231232\n";
    exit(1);
}

$numbers = explode(',', $argv[1]);

$client = new MyTgClientDebug();
/* @noinspection PhpUnhandledExceptionInspection */
$client->infoLogin();
$client->parseNumbers($numbers, true, true, function (array $models) {
    echo "Phone\t|\tUsername\t|\tFirst name\t|\tLast name\t|\tPhoto\t|\tAbout\t|\tCommon chats\t|\tLang\t|\tWas online\n\n";
    foreach ($models as $model) {
        /* @var UserInfoModel $model */
        //Logger::log('ParseNumbers', print_r($model, true));
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
            $model->langCode."\t|\t".
            $model->status->was_online."\n";
    }
});
/* @noinspection PhpUnhandledExceptionInspection */
$client->pollAndTerminate();
