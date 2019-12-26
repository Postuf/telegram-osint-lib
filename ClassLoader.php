<?php

class ClassLoader
{
    public static function load($class)
    {
        $class = str_replace(
            ['\\'],
            ['/'],
            $class
        );

        $className = __DIR__.'/src/'.$class.'.php';

        if(file_exists($className))
            require_once $className;
        else
            return false;

        return true;
    }
}

// tg lib namespaces
spl_autoload_register(__NAMESPACE__.'\\ClassLoader::load');

// dependencies namespaces (composer)
require_once __DIR__.'/vendor/autoload.php';
