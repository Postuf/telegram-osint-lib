<?php

use Client\AuthKey\Versions\AuthKey_v1_Simple;
use Client\StatusWatcherClient\StatusWatcherClient;
use Tests\Tests\Client\StatusWatcherClient\StatusWatcherClientTestCallbacks;

require_once __DIR__ . '/../../ClassLoader.php';

/** @noinspection PhpUnhandledExceptionInspection */
$client = new StatusWatcherClient(new StatusWatcherClientTestCallbacks());
$stringKey = 'TzB2PHswBzARAVmMvhw0PmexA6amKbkhvGXgUIQCc7Kf2QUDbd5rX1nIUKQfd/CTtywPtjpcaZ9774jWUJu/OozE0wVRAaj5kpo7Gse6+0HfzPMKk7o2CkeEC9F9nK573KIKLemGydlUVhT+otc7v2/+2SUtQbyBIHsUUF8qA+RK10/op3jKQTJPP3hzPVGB+zC/D0SSwVqBtp1SyTwh7IVYU0CuQcOuJ5Ainim838+bpiZeJ1OfOblzb+fGQr5PuYJ7b3upPHuBCPSQdNT3pLtxQNFZ68Ro7/UN/rlsMcps2NQcs2/QZtWmfPcDD/T4UneFvUYWl63V/p6Sa5ylATG18Hp6iluZ';
/** @noinspection PhpUnhandledExceptionInspection */
$authkey = new AuthKey_v1_Simple($stringKey);
$client->login($authkey);


//$client->sendMessage(new get_config());
//$client->sendMessage(new get_statuses());

/*while(true){
    $readMessage = $client->readMessage();
    //print_r($readMessage);
}*/