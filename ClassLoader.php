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

        if(file_exists($className)) {
            /** @noinspection PhpIncludeInspection */
            require_once $className;

            return true;
        }

        return false;
    }
}

// tg lib namespaces
spl_autoload_register(__NAMESPACE__.'\\ClassLoader::load');
