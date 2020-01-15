<?php

// tg lib loader
require_once __DIR__.'/../ClassLoader.php';

class TestClassLoader
{
    public static function load($class)
    {
        $class = str_replace(
            ['\\'],
            ['/'],
            $class
        );

        $class = str_replace('TelegramOSINT/', '', $class);
        $class = str_replace('Tests/Tests', 'Tests', $class);
        $classNames = [
            __DIR__.'/../src/'.$class.'.php',
            __DIR__.'/../tests/'.$class.'.php',
            __DIR__.'/../examples/'.$class.'.php',
        ];

        foreach ($classNames as $className) {
            if (file_exists($className)) {
                /** @noinspection PhpIncludeInspection */
                require_once $className;

                return true;
            }
        }

        return false;
    }
}

// test namespace loader
spl_autoload_register(__NAMESPACE__.'\\TestClassLoader::load');
