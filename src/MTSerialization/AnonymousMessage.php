<?php

declare(strict_types=1);

namespace TelegramOSINT\MTSerialization;

interface AnonymousMessage
{
    /**
     * Return named node from current object
     *
     * @param string $name
     *
     * @return self
     */
    public function getNode(string $name): self;

    /**
     * Return array of nodes under the $name from current object
     *
     * @param string $name
     *
     * @return self[]
     */
    public function getNodes(string $name): array;

    /**
     * Get message name
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Get value of named field from current object
     *
     * @param string $name
     *
     * @return int|string|array
     */
    public function getValue(string $name);

    /**
     * @return string
     */
    public function getPrintable(): string;

    /**
     * @return string
     */
    public function getDebugPrintable(): string;
}
