<?php

namespace TelegramOSINT\MTSerialization\OwnImplementation;

use function array_key_exists;
use function assert;
use function count;
use function hex2bin;
use function is_array;
use function is_string;
use function json_encode;
use JsonSerializable;
use function print_r;
use stdClass;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\MTSerialization\AnonymousMessage;

class OwnAnonymousMessage implements AnonymousMessage, JsonSerializable
{
    /**
     * List of known field names that contain binary values (as strings).
     *
     * Typically they are replaced with HEX values before serialization and back on deserialization.
     */
    public const BINARY_STRING_FIELD_NAMES = [
        'bytes',
        'file_reference',
    ];

    private const INTERNAL_TYPE_FIELD = '_';

    private array $object;

    private string $type;

    /**
     * @param array $deserializedByOwnArray
     */
    public function __construct(array $deserializedByOwnArray)
    {
        $this->object = $deserializedByOwnArray;
        $this->initType();
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
        if (!is_array($node)) {
            throw new TGException(TGException::ERR_TL_MESSAGE_FIELD_BAD_NODE);
        }
        assert($this->doesValueLookLikeNode($node));

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
        if (!is_array($nodes)) {
            throw new TGException(TGException::ERR_TL_MESSAGE_FIELD_BAD_NODE);
        }
        $objects = [];
        assert($this->doesValueLookLikeNodes($nodes));
        foreach ($nodes as $node) {
            assert($this->doesValueLookLikeNode($node));
            $objects[] = new self($node);
        }

        return $objects;
    }

    /**
     * Return array of scalars under the $name from current object
     *
     * @param string $name
     *
     * @throws TGException
     *
     * @return array
     */
    public function getScalars(string $name): array
    {
        $scalars = $this->getValue($name);
        if (!is_array($scalars)) {
            throw new TGException(TGException::ERR_TL_MESSAGE_FIELD_BAD_SCALARS);
        }
        assert($this->doesValueLookLikeScalars($scalars));

        return $scalars;
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
        if (!array_key_exists($name, $this->object)) {
            throw new TGException(TGException::ERR_TL_MESSAGE_FIELD_NOT_EXISTS);
        }

        return $this->object[$name];
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        // Save only object and restore type from it in wakeup.
        //
        // NOTE: that's a list of internal properties NAMES and they MUST match.
        // If you rename internal properties please update this list.
        return ['object'];
    }

    /**
     * This method is called on `unserialize()` (constructor is not called).
     */
    public function __wakeup()
    {
        $this->initType();
    }

    public function __toString()
    {
        return $this->getDebugPrintable();
    }

    public function getPrintable(): string
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return json_encode($this->object, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
    }

    public function getDebugPrintable(): string
    {
        return print_r($this->object, true);
    }

    /**
     * {@inheritdoc}
     *
     * @throws TGException
     */
    public function jsonSerialize()
    {
        $result = $this->object;

        foreach (static::BINARY_STRING_FIELD_NAMES as $name) {
            if (array_key_exists($name, $result)
                && is_string($binValue = $result[$name])
            ) {
                $result[$name] = bin2hex($binValue);
            }
        }

        // some of the fields are arrays which could be a single node, array of nodes or array of scalars
        // let's convert them accordingly
        foreach ($result as $name => $value) {
            if ($this->doesValueLookLikeNode($value)) {
                $result[$name] = $this->getNode($name);
            } elseif ($this->doesValueLookLikeNodes($value)) {
                $result[$name] = $this->getNodes($name);
            } else {
                assert(!is_array($value) || $this->doesValueLookLikeScalars($value));
            }
        }

        return $result;
    }

    /**
     * @param stdClass $json
     *
     * @return self
     */
    public static function jsonDeserialize(stdClass $json): self
    {
        // This function mirrors `jsonSerialize`.
        // It will recursively go through input json and convert each HEXed string back to binary one.
        // Also it will convert every stdClass to array.
        $fastSearchBinaryFieldNames = array_flip(static::BINARY_STRING_FIELD_NAMES);
        $ownArray = self::deserializeFieldsRecursively((array) $json, $fastSearchBinaryFieldNames);

        return new self($ownArray);
    }

    /**
     * Helper function for JSON deserialization.
     *
     * @param array $fields
     * @param array $fastSearchBinaryFieldNames
     *
     * @return array
     */
    private static function deserializeFieldsRecursively(array $fields, array $fastSearchBinaryFieldNames): array
    {
        foreach ($fields as $name => $value) {
            if (array_key_exists($name, $fastSearchBinaryFieldNames)) {
                assert(is_string($value));
                $fields[$name] = hex2bin($value);
            } elseif (is_array($value)) {
                $fields[$name] = self::deserializeFieldsRecursively($value, $fastSearchBinaryFieldNames);
            } elseif ($value instanceof stdClass) {
                $fields[$name] = self::deserializeFieldsRecursively((array) $value, $fastSearchBinaryFieldNames);
            } else {
                assert($value === null || is_scalar($value));
            }
        }

        return $fields;
    }

    /**
     * Take actual type from an internal object and set property.
     */
    private function initType(): void
    {
        $this->type = $this->object[static::INTERNAL_TYPE_FIELD];
    }

    /**
     * @param array $value
     *
     * @return bool
     */
    private function doesArrayHasType(array $value): bool
    {
        return array_key_exists(static::INTERNAL_TYPE_FIELD, $value);
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    private function doesValueLookLikeNode($value): bool
    {
        return is_array($value) && $this->doesArrayHasType($value);
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    private function doesValueLookLikeNodes($value): bool
    {
        // is array, has no type, either empty array or first item looks like a node
        return is_array($value)
            && !$this->doesArrayHasType($value)
            && (count($value) === 0 || $this->doesValueLookLikeNode(reset($value)));
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    private function doesValueLookLikeScalars($value): bool
    {
        // is array, has no type, either empty array or first item does NOT looks like a node
        return is_array($value)
            && !$this->doesArrayHasType($value)
            && (count($value) === 0 || !$this->doesValueLookLikeNode(reset($value)));
    }

    public function hasNode(string $name): bool
    {
        return isset($this->object[$name]);
    }
}
