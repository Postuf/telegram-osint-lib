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

$mapStore = __DIR__.'/../src/MTSerialization/OwnImplementation/maps';
$maps = array_diff(
    scandir($mapStore),
    ['.', '..']
);

// find layer_compiled_XXX.json with max layer and sort simple layer files (layer_XXX.json) by version
$compiledLayerFiles = [];
$simpleLayerFiles = [];
foreach ($maps as $mapFile) {
    preg_match("/^layer_compiled_(\d+)\.json$/i", $mapFile, $matches);
    if (sizeof($matches) > 0) {
        $compiledLayerFiles[$matches[1]] = $mapFile;
    }
    preg_match("/^layer_(\d+)\.json$/i", $mapFile, $matches);
    if (sizeof($matches) > 0) {
        $simpleLayerFiles[$matches[1]] = $mapFile;
    }
}
krsort($compiledLayerFiles);
ksort($simpleLayerFiles);

$resultMap = ['constructors' => [], 'methods' => []];
$lastLayer = 0;
// last compiled layer file is the most recent
foreach ($compiledLayerFiles as $layer => $mapFile) {
    getMapNodes($mapStore.'/'.$mapFile, $resultMap);
    $lastLayer = $layer;
    echo "Layer $layer\n";
    break;
}
// process simple layer files greater than last compiled layer
foreach ($simpleLayerFiles as $layer => $mapFile) {
    if ($layer > $lastLayer) {
        getMapNodes($mapStore.'/'.$mapFile, $resultMap);
        echo "Layer $layer\n";
    }
}

$resultMap['constructors'] = array_values($resultMap['constructors']);
$resultMap['methods'] = array_values($resultMap['methods']);
$compiled = json_encode($resultMap, JSON_PRETTY_PRINT);
file_put_contents($mapStore.'/compiled.json', $compiled);
