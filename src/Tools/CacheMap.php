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
            $this->map = json_decode(file_get_contents($filename), true);
        }
        $this->filename = $filename;
    }

    public function set(string $key, $value): void {
        $this->map[$key] = $value;
        file_put_contents($this->filename, json_encode($this->map, JSON_PRETTY_PRINT));
    }

    public function get(string $key) {
        return isset($this->map[$key]) ? $this->map[$key] : null;
    }
}
