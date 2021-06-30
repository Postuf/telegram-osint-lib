<?php

function getMapNodes(string $filename, array &$map)
{
    $raw_entities = json_decode(file_get_contents($filename), true);
    $constructors = $raw_entities['constructors'];
    $methods = $raw_entities['methods'];

    foreach ($constructors as $constructor) {
        $id = hexdec(str_ireplace('ffffffff', '', dechex($constructor['id'])));
        $map['constructors'][$id] = $constructor;
    }
    foreach ($methods as $method) {
        $id = hexdec(str_ireplace('ffffffff', '', dechex($method['id'])));
        $map['methods'][$id] = $method;
    }
}

$mapStore = __DIR__ . "/../src/MTSerialization/OwnImplementation/maps";
$maps = array_diff(
    scandir($mapStore),
    array('.', '..')
    );
$resultMap = [];
foreach ($maps as $mapFile){
    $map = getMapNodes($mapStore.'/'.$mapFile, $resultMap);
}

$resultMap['constructors'] = array_values($resultMap['constructors']);
$resultMap['methods'] = array_values($resultMap['methods']);
$compiled = json_encode($resultMap, JSON_PRETTY_PRINT);
file_put_contents($mapStore . '/compiled.json', $compiled);