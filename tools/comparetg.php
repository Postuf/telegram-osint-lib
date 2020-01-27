<?php

// compare diff tool
// used to find new constructors/methods when protocol is updated

const CONSTRUCTORS = 'constructors';
const METHODS = 'methods';
const ARG_PREDICATE = 'PREDICATE';
const ARG_RAW = '--raw';
const ARG_DEBUG = '--debug';

if (!isset($argv[1])) {
    fprintf(STDERR, "please specify input file name/dir (old)\n");
    exit(1);
}

if (!isset($argv[2])) {
    fprintf(STDERR, "please specify input file name new|PREDICATE\n");
    exit(1);
}

$isRaw = (isset($argv[3]) && $argv[3] == ARG_RAW)
    || (isset($argv[4]) && $argv[4] == ARG_RAW);
$isDebug = (isset($argv[3]) && $argv[3] == ARG_DEBUG)
    || (isset($argv[4]) && $argv[4] == ARG_DEBUG);

function getJson($filename) {
    global $isDebug;
    if ($isDebug) {
        echo "load $filename\n";
    }
    $json = json_decode(file_get_contents($filename), true);
    if (!$json) {
        fprintf(STDERR, "invalid json $filename\n");
        exit(1);
    }

    $constructors = $json[CONSTRUCTORS];
    $methods = $json[METHODS];
    $constructorsById = [];
    $methodsById = [];
    foreach ($constructors as $constructor) {
        $constructor['filename'] = $filename;
        $constructorsById[$constructor['id']] = $constructor;
    }
    foreach ($methods as $method) {
        $methodsById[$method['id']] = $method;
    }
    asort($constructorsById);
    asort($methodsById);
    $json[CONSTRUCTORS] = $constructorsById;
    $json[METHODS] = $methodsById;

    return $json;
}

$json1 = [];
$processJson = function ($filename, bool $isRaw = false) use (&$json1, $isDebug) {
    if ($isDebug) {
        $countConst = count($json1[CONSTRUCTORS]);
        $countMethod = count($json1[METHODS]);
        echo "compare to: $countConst constructors, $countMethod methods\n";
    }

    $json2 = getJson($filename);
    if ($isDebug) {
        $countConst = count($json2[CONSTRUCTORS]);
        $countMethod = count($json2[METHODS]);
        echo "loaded total (json2): $countConst constructors, $countMethod methods\n";
    }

    $keys = [METHODS, CONSTRUCTORS];
    foreach ($keys as $key) {
        foreach ($json2[$key] as $k => $constructor) {
            if (isset($json1[$key][$k])) {
                unset($json2[$key][$k]);
            }
        }
        if ($isRaw) {
            $json2[$key] = array_values($json2[$key]);
        }
    }
    if ($isDebug) {
        $countConst = count($json2[CONSTRUCTORS]);
        $countMethod = count($json2[METHODS]);
        echo "after filter (json2): $countConst constructors, $countMethod methods\n";
    }

    die(json_encode($json2, JSON_PRETTY_PRINT));
};

$json1 = [];
if (is_dir($argv[1])) {
    $dir = new DirectoryIterator($argv[1]);
    foreach ($dir as $fileinfo) {
        if ($fileinfo->isDot() || $fileinfo->isDir()) {
            continue;
        }
        if (strpos($fileinfo->getFilename(), '.swp') !== false) {
            continue;
        }
        $partJson = getJson($argv[1].DIRECTORY_SEPARATOR.$fileinfo->getFilename());
        $json1 = array_replace_recursive($json1, $partJson);
    }
    if ($isDebug) {
        $countConst = count($json1[CONSTRUCTORS]);
        $countMethod = count($json1[METHODS]);
        echo "loaded total: $countConst constructors, $countMethod methods\n";
    }
} else {
    $json1 = getJson($argv[1]);
}

$checkPredicates = function ($json) {
    $predicatesByType = [];
    foreach ($json[CONSTRUCTORS] as $constructor) {
        if (!isset($constructor['type'])) {
            continue;
        }
        if (!isset($predicatesByType[$constructor['type']])) {
            $predicatesByType[$constructor['type']] = [];
        }
        if (isset($predicatesByType[$constructor['type']][$constructor['predicate']])) {
            $existing = $predicatesByType[$constructor['type']][$constructor['predicate']];
            $same = '';
            if ($constructor['id'] === $existing['id']) {
                $same = 'with same id ';
            }
            echo "WARN: duplicate {$same}predicate {$constructor['predicate']} with id {$constructor['id']} for type {$constructor['type']} in {$constructor['filename']}\n";
            echo "WARN: existing predicate {$existing['predicate']} with id {$existing['id']} for type {$existing['type']} in {$existing['filename']}\n";
        }
        $predicatesByType[$constructor['type']][$constructor['predicate']] = $constructor;
    }
};

if ($argv[2] === ARG_PREDICATE) {
    $checkPredicates($json1);
} else {
    $processJson($argv[2], $isRaw);
}
