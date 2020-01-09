<?php
declare(strict_types=1);

use Exception\TGException;

require_once __DIR__ . "/../../ClassLoader.php";

$codeGroup = 100000;
$localCounter = 0;

foreach((new TGException())->getConstants() as $code => $constant){

    if($code >= $codeGroup+100000) {
        $codeGroup += 100000;
        $localCounter = 0;
        echo "\n";
    }

    $calculatedCode = $codeGroup + $localCounter;
    $localCounter += 1000;

    $name = 'const '.$constant;
    $len = strlen($name);
    echo $name . str_pad('', 75-$len, " ") . '= '.$calculatedCode.";\n";

}