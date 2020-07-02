<?php

declare(strict_types=1);

namespace TelegramOSINT\Registration\NameGenerator;

class NameResource
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $lastName;
    /** @var array */
    private static $names = [];

    public function __construct()
    {
        if (!self::$names) {
            /** @noinspection PhpUnhandledExceptionInspection */
            self::$names = json_decode(file_get_contents(__DIR__.'/names.json'), true, 512, JSON_THROW_ON_ERROR);
        }

        $this->name = self::$names[array_rand(self::$names)];
        $this->lastName = self::$names[array_rand(self::$names)];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }
}
