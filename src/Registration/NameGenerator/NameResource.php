<?php

namespace Registration\NameGenerator;

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

    public function __construct()
    {
        $names = json_decode(file_get_contents(__DIR__.'/names.json'), true);

        $this->name = $names[array_rand($names)];
        $this->lastName = $names[array_rand($names)];

        $names = [];
        unset($names);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }
}
