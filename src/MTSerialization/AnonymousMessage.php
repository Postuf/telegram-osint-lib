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
     * Return if named node from current object exists
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasNode(string $name): bool;

    /**
     * Return array of nodes under the $name from current object
     *
     * @param string $name
     *
     * @return self[]
     */
    public function getNodes(string $name): array;

    /**
     * Return array of scalars under the $name from current object
     *
     * @param string $name
     *
     * @return array
     */
    public function getScalars(string $name): array;

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

    public function getPrintable(): string;

    public function getDebugPrintable(): string;
}
