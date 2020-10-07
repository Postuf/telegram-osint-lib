<?php

// compare diff tool
// used to find new constructors/methods when protocol is updated

const CONSTRUCTORS = 'constructors';
const METHODS = 'methods';
const ARG_PREDICATE = 'PREDICATE';
const ARG_RAW = '--raw';
const ARG_DEBUG = '--debug';

const MAX_POSITIVE_INT = 2147483647;
const INT_SUBTRACT = 0x100000000;

if (!isset($argv[1])) {
    fprintf(STDERR, "please specify input file name/dir (old)\n");
    exit(1);
}

if (!isset($argv[2])) {
    fprintf(STDERR, "please specify input file name new|PREDICATE\n");
    exit(1);
}

$isRaw = (isset($argv[3]) && $argv[3] === ARG_RAW)
    || (isset($argv[4]) && $argv[4] === ARG_RAW);
$isDebug = (isset($argv[3]) && $argv[3] === ARG_DEBUG)
    || (isset($argv[4]) && $argv[4] === ARG_DEBUG);

/**
 * @param $filename
 *
 * @throws JsonException
 *
 * @return mixed
 */
function getJson($filename)
{
    global $isDebug;
    if ($isDebug) {
        echo "load $filename\n";
    }
    $json = json_decode(file_get_contents($filename), true, 512, JSON_THROW_ON_ERROR);
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
        if ($constructor['id'] > MAX_POSITIVE_INT) {
            $constructor['id'] -= INT_SUBTRACT;
        }
        $constructorsById[$constructor['id']] = $constructor;
    }
    foreach ($methods as $method) {
        if ($method['id'] > MAX_POSITIVE_INT) {
            $method['id'] -= INT_SUBTRACT;
        }
        $methodsById[$method['id']] = $method;
    }
    asort($constructorsById);
    asort($methodsById);
    $json[CONSTRUCTORS] = $constructorsById;
    $json[METHODS] = $methodsById;

    return $json;
}

$json1 = [];
$processJson = static function ($filename, bool $isRaw = false) use (&$json1, $isDebug) {
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

    foreach ([METHODS, CONSTRUCTORS] as $key) {
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

    exit(json_encode($json2, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
};

$json1 = [];
if (is_dir($argv[1])) {
    $dir = new DirectoryIterator($argv[1]);
    foreach ($dir as $fileInfo) {
        if ($fileInfo->isDot() || $fileInfo->isDir()) {
            continue;
        }
        if (strpos($fileInfo->getFilename(), '.swp') !== false) {
            continue;
        }
        /** @noinspection PhpUnhandledExceptionInspection */
        $partJson = getJson($argv[1].DIRECTORY_SEPARATOR.$fileInfo->getFilename());
        /** @noinspection SlowArrayOperationsInLoopInspection */
        $json1 = array_replace_recursive($json1, $partJson);
    }
    if ($isDebug) {
        $countConst = count($json1[CONSTRUCTORS]);
        $countMethod = count($json1[METHODS]);
        echo "loaded total: $countConst constructors, $countMethod methods\n";
    }
} else {
    /** @noinspection PhpUnhandledExceptionInspection */
    $json1 = getJson($argv[1]);
}

$checkPredicates = static function ($json) {
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
