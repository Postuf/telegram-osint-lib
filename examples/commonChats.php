<?php

declare(strict_types=1);

use TelegramOSINT\Scenario\CommonChatsScenario;
use TelegramOSINT\Scenario\ReusableClientGenerator;

require_once __DIR__.'/../vendor/autoload.php';

if (!isset($argv[1])){
    echo "please specify number: 79001234567\n";
    exit(1);
}

$phone = $argv[1];

$chatMap = [
    'twochannel'              => ['новости', 'развлечения'],
    'durclub'                 => ['развлечения', 'игры'],
    'ateo_chat'               => ['развлечения', 'новости'],
    'chop_chop_xxx'           => ['развлечения'],
    'flibustafreebookbot_new' => ['книги'],
    'znakomstva_chats'        => ['знакомства'],
    'raidshadowlegend'        => ['игры'],
    //    'ru2chnews' => ['развлечения'],
    //    'danludan_chat' => ['gambling'],
    //    'chat_exam' => ['учеба'],
    //    'savemdk' => ['развлечения', 'новости'],
    //    'chat_3hakomctba_18' => ['знакомства'],
    //    'flibustafreebookboto' => ['книги'],
    //    'balichatik' => ['Бали'],
    //    'crocodileclub' => ['развлечения'],
    //    'the_it_kings' => ['it'],
    //    'lankaru' => ['Шри-Ланка'],
    //    'jinochat' => ['знакомства'],
    //    'grow_chat' => ['гровинг'],
    //    'kino_chat' => ['кино'],
    //    'mafiawar' => ['развлечения'],
    //    'mobilepubguz' => ['развлечения', 'игры'],
    //    'binancerussian' => ['криптовалюты'],
    //    'znsbp' => ['знакомства', 'Санкт-Петербург'],
    //    'yobit_chat' => ['криптовалюты'],
    //    'chat_msk' => ['Москва'],
    //    'muzykachat' => ['музыка'],
    //    'michat' => ['технологии'],
    //    'emcdpool' => ['криптовалюты'],
    //    'news1xb_chat' => ['gambling'],
    //    'mafiagameru' => ['развлечения'],
    //    'smartpunters' => ['gambling'],
    //    'poshlyichat' => ['развлечения'],
    //    'sheltermafia' => ['развлечения'],
    //    'seregasoleniy' => ['криптовалюты'],
    //    'bk_newschat1st' => ['gambling'],
    //    'anime2ch' => ['аниме'],
    //    'poliitach' => ['политика'],
    //    'findkievznakomstva' => ['Киев', 'знакомства'],
    //    'cparipchat' => ['бизнес', 'арбитраж'],
    //    'pinupchat' => ['gambling'],
];

$callback = function ($interests) use ($phone) {
    arsort($interests);
    echo 'Phone number: '.$phone."\n";
    echo "Interest\t|\tWeight\n";
    foreach ($interests as $title => $rating) {
        echo $title."\t|\t".$rating."\n";
    }
};

$generator = new ReusableClientGenerator();
$scenario = new CommonChatsScenario(
    $generator,
    $chatMap,
    $phone,
    $callback
);
$scenario->startActions();
