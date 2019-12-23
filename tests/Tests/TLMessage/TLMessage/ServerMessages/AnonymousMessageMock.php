<?php

namespace Tests\Tests\TLMessage\TLMessage\ServerMessages;


use Exception\TGException;
use MTSerialization\AnonymousMessage;
use MTSerialization\OwnImplementation\OwnAnonymousMessage;


class AnonymousMessageMock implements AnonymousMessage
{

    /**
     * @var AnonymousMessage
     */
    private $impl;


    /**
     * @param array $message
     * @throws TGException
     */
    public function __construct(array $message)
    {
        $this->impl = new OwnAnonymousMessage($message);
    }


    /**
     * Return named node from current object
     *
     * @param string $name
     * @return AnonymousMessage
     * @throws TGException
     */
    public function getNode(string $name)
    {
        return $this->impl->getNode($name);
    }

    /**
     * Return array of nodes under the $name from current object
     *
     * @param string $name
     * @return AnonymousMessage[]
     * @throws TGException
     */
    public function getNodes(string $name)
    {
        return $this->impl->getNodes($name);
    }

    /**
     * Get message name
     *
     * @return string
     */
    public function getType()
    {
        return $this->impl->getType();
    }

    /**
     * Get value of named field from current object
     *
     * @param string $name
     * @return int|string|array
     * @throws TGException
     */
    public function getValue(string $name)
    {
        return $this->impl->getValue($name);
    }

    /**
     * @return string
     */
    public function getPrintable()
    {
        return $this->impl->getPrintable();
    }

    /**
     * @return string
     */
    public function getDebugPrintable()
    {
        return $this->impl->getDebugPrintable();
    }


}