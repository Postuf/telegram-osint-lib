<?php

namespace TelegramOSINT\MTSerialization\OwnImplementation;

use TelegramOSINT\Exception\TGException;
use TelegramOSINT\MTSerialization\AnonymousMessage;

class OwnAnonymousMessage implements AnonymousMessage
{
    /**
     * @var array
     */
    private $object;
    private $type;

    /**
     * @param array $deserializedByOwnArray
     *
     * @throws TGException
     */
    public function __construct(array $deserializedByOwnArray)
    {
        if(!is_array($deserializedByOwnArray))
            throw new TGException(TGException::ERR_TL_MESSAGE_FIELD_BAD_NODE);
        $this->object = $deserializedByOwnArray;
        $this->type = isset($this->object['_']) ? $this->object['_'] : null;
    }

    /**
     * Return named node from current object
     *
     * @param string $name
     *
     * @throws TGException
     *
     * @return AnonymousMessage
     */
    public function getNode(string $name): AnonymousMessage
    {
        $node = $this->getValue($name);
        if(!is_array($node))
            throw new TGException(TGException::ERR_TL_MESSAGE_FIELD_BAD_NODE);

        return new self($node);
    }

    /**
     * Return array of nodes under the $name from current object
     *
     * @param string $name
     *
     * @throws TGException
     *
     * @return AnonymousMessage[]
     */
    public function getNodes(string $name): array
    {
        $nodes = $this->getValue($name);
        if(!is_array($nodes))
            throw new TGException(TGException::ERR_TL_MESSAGE_FIELD_BAD_NODE);
        $objects = [];
        foreach ($nodes as $node)
            $objects[] = new self($node);

        return $objects;
    }

    /**
     * Get message name
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get value of named field from current object
     *
     * @param string $name
     *
     * @throws TGException
     *
     * @return int|string|array
     */
    public function getValue(string $name)
    {
        if(!array_key_exists($name, $this->object))
            throw new TGException(TGException::ERR_TL_MESSAGE_FIELD_NOT_EXISTS);

        return $this->object[$name];
    }

    public function __toString()
    {
        return $this->getDebugPrintable();
    }

    public function getPrintable(): string
    {
        return json_encode($this->object, JSON_PRETTY_PRINT);
    }

    public function getDebugPrintable(): string
    {
        return print_r($this->object, true);
    }
}
