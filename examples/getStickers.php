<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use TelegramOSINT\Client\InfoObtainingClient\Models\StickerSetModel;
use TelegramOSINT\Scenario\FeaturedStickerSetScenario;
use TelegramOSINT\Scenario\GetStickerSetScenario;
use TelegramOSINT\Scenario\Models\StickerSetId;
use TelegramOSINT\Scenario\ReusableClientGenerator;
use TelegramOSINT\Scenario\StickerClientGenerator;

require_once __DIR__.'/../vendor/autoload.php';

$generator = new ReusableClientGenerator(new StickerClientGenerator());
$echo = static function (StickerSetModel $set) {
    echo "got sticker set {$set->getId()}".PHP_EOL;
};
$fn = static function (StickerSetModel $set) use ($echo, $generator) {
    $scenario = new GetStickerSetScenario(
        StickerSetId::of($set),
        $echo,
        false,
        $generator
    );
    $scenario->startActions(false);
};
$scenario = new FeaturedStickerSetScenario($fn, $generator);
$scenario->startActions();
