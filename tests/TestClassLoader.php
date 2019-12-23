<?php

// tg lib loader
require_once __DIR__ . "/../ClassLoader.php";

class TestClassLoader
{
    public static function load($class)
    {
        $class = str_replace(
            array( "\\"),
            array( '/'),
            $class
        );

        $class = str_replace('Tests/Tests', 'Tests', $class);
        $className = __DIR__."/".$class.'.php';

        if(file_exists($className))
            require_once $className;
        else
            return false;

        return true;
    }
}


// test namespace loader
spl_autoload_register(__NAMESPACE__ . "\\TestClassLoader::load");
