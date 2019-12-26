<?php

namespace MTSerialization;

interface AnonymousMessage
{
    /**
     * Return named node from current object
     *
     * @param string $name
     *
     * @return AnonymousMessage
     */
    public function getNode(string $name);

    /**
     * Return array of nodes under the $name from current object
     *
     * @param string $name
     *
     * @return AnonymousMessage[]
     */
    public function getNodes(string $name);

    /**
     * Get message name
     *
     * @return string
     */
    public function getType();

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
    public function getPrintable();

    /**
     * @return string
     */
    public function getDebugPrintable();
}
