<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use TelegramOSINT\Client\InfoObtainingClient\Models\MessageModel;
use TelegramOSINT\Logger\Logger;
use TelegramOSINT\Scenario\GroupMessagesScenario;
use TelegramOSINT\Scenario\GroupResolverScenario;
use TelegramOSINT\Scenario\Models\GroupId;
use TelegramOSINT\Scenario\Models\GroupRequest;
use TelegramOSINT\Scenario\Models\OptionalDateRange;
use TelegramOSINT\Scenario\ReusableClientGenerator;

const INFO = '--info';

/** @var int|null $groupId */
$groupId = null;
/** @var int|null $timestampStart */
$timestampStart = null;
/** @var int|null $timestampEnd */
$timestampEnd = null;

if (!isset($argv[1]) || isset($argv[1]) && $argv[1] === '--help') {
    echo <<<'TXT'
Usage: php parseChannelLinks.php groupId|deepLink [timestampStart] [timestampEnd] [--info]
    deepLink ex.: https://t.me/vityapelevin
TXT;
    die();
}

if ($argv[1] !== INFO) {
    if (is_numeric($argv[1])) {
        $groupId = (int) $argv[1];
    } else {
        $deepLink = $argv[1];
    }
}

if (isset($argv[2]) && $argv[2] !== INFO) {
    $timestampStart = (int) $argv[2];
}

if (isset($argv[3]) && $argv[3] !== INFO) {
    $timestampEnd = (int) $argv[3];
}

$generator = new ReusableClientGenerator();
$request = $groupId
    ? GroupRequest::ofGroupId($groupId)
    : GroupRequest::ofUserName($deepLink);

$result = [];
$parseLinks = function (?MessageModel $messageModel, ?array $messageRaw, int $endFlag) use (&$result) {
    if ($endFlag == -1){
        arsort($result, SORT_NUMERIC);

        echo "\tSite\t|\tLinks count\t\n";
        foreach ($result as $site => $count) {
            echo $site."\t|\t".$count."\n";
        }
    } else {
        if (!empty($messageRaw['message'])) {
            if (preg_match('/http[s]?:\/\/([\w.\-_\d]*)/', $messageRaw['message'], $matches)) {
                if (!empty($matches[1])) {
                    $domain = $matches[1];
                    $result[$domain] = !empty($result[$domain]) ? $result[$domain] + 1 : 1;
                }
            }
        }
    }
};

$onGroupReady = function (?int $groupId, ?int $accessHash) use ($timestampStart, $timestampEnd, $generator, $parseLinks) {
    if (!$groupId) {
        Logger::log('parseChannelLinks', 'Group not found');

        return;
    }

    $client = new GroupMessagesScenario(
        new GroupId($groupId, $accessHash),
        $generator,
        new OptionalDateRange(
            $timestampStart,
            $timestampEnd
        ),
        $parseLinks
    );
    $client->startLinkParse();
};

$resolver = new GroupResolverScenario($request, $generator, $onGroupReady);
/** @noinspection PhpUnhandledExceptionInspection */
$resolver->startActions();
