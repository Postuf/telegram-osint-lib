<?php

declare(strict_types=1);

namespace TelegramOSINT\Tools;

class CacheMap
{
    /** @var string */
    private $filename;
    /** @var array */
    private $map = [];

    public function __construct(string $filename)
    {
        if (file_exists($filename)) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->map = json_decode(file_get_contents($filename), true, 512, JSON_THROW_ON_ERROR);
        }
        $this->filename = $filename;
    }

    public function set(string $key, $value): void {
        $this->map[$key] = $value;
        /** @noinspection PhpUnhandledExceptionInspection */
        file_put_contents($this->filename, json_encode($this->map, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
    }

    public function get(string $key) {
        return $this->map[$key] ?? null;
    }
}
