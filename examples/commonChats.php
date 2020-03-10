<?php

declare(strict_types=1);

use TelegramOSINT\Scenario\CommonChatsScenario;
use TelegramOSINT\Scenario\ReusableClientGenerator;

require_once __DIR__.'/../vendor/autoload.php';

$argsOrFalse = getopt('n:h', ['number:', 'help']);
if ($argsOrFalse === false
    || (array_key_exists('h', $argsOrFalse) || array_key_exists('help', $argsOrFalse))
    || (!array_key_exists('n', $argsOrFalse) && !array_key_exists('number', $argsOrFalse))
) {
    echo <<<'EOT'
Usage:
    php commonChats.php -n number
    php commonChats.php --number number

   -n, --number                 Phone number (e.g. 1234567890).
   -h, --help                   Display this help message.

EOT;
    exit(1);
}

$phone = $argsOrFalse['n'] ?? $argsOrFalse['number'];

$chatMap = [
    'twochannel'              => ['политика', 'развлечения'],
    'durclub'                 => ['развлечения', 'игры'],
    'ateo_chat'               => ['развлечения', 'политика'],
    'chop_chop_xxx'           => ['развлечения'],
    'flibustafreebookbot_new' => ['книги'],
    'znakomstva_chats'        => ['знакомства'],
    'phuketrusa'              => ['путешествия', 'пхукет'],
    //    'raidshadowlegend'        => ['игры'],
    //    'raidshadowlegend' => ['игры'],
    //    'ru2chnews' => ['развлечения'],
    //    'danludan_chat' => ['gambling'],
    //    'chat_exam' => ['учеба'],
    //    'savemdk' => ['развлечения'],
    //    'chat_3hakomctba_18' => ['знакомства'],
    //    'flibustafreebookboto' => ['книги'],
    //    'balichatik' => ['Бали'],
    //    'crocodileclub' => ['развлечения'],
    //    'flibustafreebookbot_new' => ['книги'],
    //    'chop_chop_xxx' => ['развлечения'],
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
/** @noinspection PhpUnhandledExceptionInspection */
$scenario = new CommonChatsScenario(
    $generator,
    $chatMap,
    $phone,
    $callback
);
/** @noinspection PhpUnhandledExceptionInspection */
$scenario->startActions();
