#!/usr/bin/php
<?php
if (!isset($argv[1]) || !file_exists($argv[1])) {
    die("Please supply a valid filename\n");
}
$filename = $argv[1];
$contents = file_get_contents($filename);
$json = json_decode($contents, true);
$delta = 0.05;
$startTs = $json[0];
$lastTs = $startTs;
foreach ($json[1] as &$record) {
    $record[2] = $lastTs + $delta;
    $lastTs += $delta;
}
file_put_contents($filename, json_encode($json, JSON_PRETTY_PRINT));
