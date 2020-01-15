<?php

namespace TelegramOSINT\MTSerialization\OwnImplementation;

use TelegramOSINT\Exception\TGException;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\MTSerialization\MTDeserializer;

class OwnDeserializer implements MTDeserializer
{
    /**
     * Static in order to decrease memory consumption if multiple instances
     *
     * @var array
     */
    private static $map = [];
    private static $mapLoaded = false;

    /**
     * @var ByteStream
     */
    private $stream;

    public function __construct()
    {
        if (!self::$mapLoaded) {
            $this->extendMap(file_get_contents(__DIR__.'/maps/official.json'));
            $this->extendMap(file_get_contents(__DIR__.'/maps/tg_app_old.json'));
            $this->extendMap(file_get_contents(__DIR__.'/maps/tg_app.json'));
            $this->extendMap(file_get_contents(__DIR__.'/maps/unclassified.json'));
            $this->extendMap(file_get_contents(__DIR__.'/maps/layer_82.json'));
            $this->extendMap(file_get_contents(__DIR__.'/maps/layer_92.json'));
            $this->extendMap(file_get_contents(__DIR__.'/maps/layer_96.json'));
            $this->extendMap(file_get_contents(__DIR__.'/maps/layer_97.json'));
            $this->extendMap(file_get_contents(__DIR__.'/maps/layer_98.json'));
            $this->extendMap(file_get_contents(__DIR__.'/maps/layer_101.json'));
            $this->extendMap(file_get_contents(__DIR__.'/maps/layer_104.json'));
            $this->extendMap(file_get_contents(__DIR__.'/maps/layer_105.json'));
            self::$mapLoaded = true;
        }
    }

    /**
     * @param string $mapContents
     */
    protected function extendMap(string $mapContents)
    {
        $entities = [];
        foreach ($this->decodeMap($mapContents) as $scope => $_entities) {
            foreach ($_entities as $_entity) {
                $entities[] = $_entity;
            }
        }

        foreach ($entities as $entity) {
            $id = hexdec(str_ireplace('ffffffff', '', dechex($entity['id'])));
            $name = $entity['predicate'] ?? $entity['method'];
            $params = $entity['params'];

            self::$map[$id] = [
                'name' => $name,
                'args' => $params,
            ];
        }
    }

    /**
     * @param string $map
     *
     * @return array
     */
    private function decodeMap(string $map)
    {
        return json_decode($map, true);
    }

    /**
     * @param string $data
     *
     * @throws TGException
     *
     * @return AnonymousMessage
     */
    public function deserialize(string $data)
    {
        $object = $this->deserializeInternal($data);

        return new OwnAnonymousMessage($object);
    }

    /**
     * @param string $data
     *
     * @throws TGException
     *
     * @return array
     */
    private function deserializeInternal(string $data)
    {
        $this->stream = new ByteStream($data);

        $object = $this->readObject();

        if (!$this->stream->isEmpty()) {
            throw new TGException(TGException::ERR_DESERIALIZER_NOT_TOTAL_READ, 'left: '.bin2hex($this->stream));
        }

        return $object;
    }

    /**
     * @throws TGException
     *
     * @return array
     */
    private function readObject()
    {
        $id = $this->readId();

        return $this->readObjectWithId($id);
    }

    /**
     * @param int $id
     *
     * @throws TGException
     *
     * @return array
     */
    private function readObjectWithId(int $id): array
    {
        if (!isset(self::$map[$id])) {
            $idHex = bin2hex(pack('I', $id));

            throw new TGException(TGException::ERR_DESERIALIZER_UNKNOWN_OBJECT, 'object with id not found: '.$idHex);
        }

        $object = self::$map[$id];

        return $this->createObject($object);
    }

    /**
     * @param array $object
     *
     * @throws TGException
     *
     * @return array
     */
    private function createObject(array $object)
    {
        $name = $object['name'];

        $bundle = [];
        $bundle['_'] = $name;

        if ($name == 'msg_container') {
            return array_merge($bundle, $this->readMsgContainer());
        }

        if ($name == 'vector') {
            return array_merge($bundle, $this->readVectorAsObject());
        }

        $this->readObjectFields($object, $bundle);

        if ($name == 'gzip_packed') {
            $bundle = $this->deserializeInternal(gzdecode($bundle['packed_data']));
        }

        return $bundle;
    }

    /**
     * @param array $object
     * @param array $bundle
     *
     * @throws TGException
     */
    private function readObjectFields(array $object, array &$bundle)
    {
        foreach ($object['args'] as $objectArg) {
            $this->readObjectField($objectArg, $bundle);
        }
    }

    /**
     * @param array $objectArg
     * @param array $bundle
     *
     * @throws TGException
     */
    private function readObjectField(array $objectArg, array &$bundle)
    {
        $isObjectArgOptional = $this->isObjectArgOptional($objectArg);

        if ($isObjectArgOptional) {
            if (!array_key_exists('flags', $bundle)) {
                throw new TGException(
                    TGException::ERR_DESERIALIZER_FIELD_BIT_MASK_NOT_PROVIDED,
                    print_r($bundle, true)
                );
            }

            $objectArgValue = $this->readOptionalField($objectArg, $bundle['flags']);
        } else {
            $objectArgValue = $this->readTypedField($objectArg['type']);
        }

        $objectArgName = $objectArg['name'];
        $bundle[$objectArgName] = $objectArgValue;
    }

    /**
     * @param array $arg
     *
     * @return bool
     */
    private function isObjectArgOptional(array $arg): bool
    {
        return substr($arg['type'], 0, 5) === 'flags';
    }

    /**
     * @param array $fieldBit
     * @param int   $bitMask
     *
     * @throws TGException
     *
     * @return array|bool|null|string
     */
    private function readOptionalField(array $fieldBit, int $bitMask)
    {
        list($bitIndex, $type) = $this->readFlagInfoForOptionalField($fieldBit);

        $bitToCheck = 1 << $bitIndex;
        $flag = 0 !== ($bitMask & $bitToCheck);
        $isFlagField = strstr($fieldBit['type'], '?true');

        return $isFlagField ?
            $flag : ($flag ? $this->readTypedField($type) : null);
    }

    /**
     * @param array $fieldBit
     *
     * @return array
     */
    private function readFlagInfoForOptionalField(array $fieldBit): array
    {
        list($objectArgFlagInfo, $objectArgType) = explode('?', $fieldBit['type']);

        $flagInfo = explode('.', $objectArgFlagInfo);

        return [(int) $flagInfo[1], $objectArgType];
    }

    /**
     * @throws TGException
     *
     * @return array
     */
    private function readMsgContainer()
    {
        $msgCount = $this->readInt();
        $bundle['declared_count'] = $msgCount;
        $bundle['messages'] = [];

        for ($i = 0; $i < $msgCount; $i++) {

            // telegram miscounted messages
            if ($this->stream->isEmpty()) {
                $bundle['real_count'] = $i;
                break;
            }

            $bundle['messages'][$i] = [];
            $bundle['messages'][$i]['msg_id'] = $this->readLong();
            $bundle['messages'][$i]['seqno'] = $this->readInt();
            $bundle['messages'][$i]['length'] = $this->readInt();
            // rewrite everything above
            $bundle['messages'][$i] = $this->readObject();
        }

        return $bundle;
    }

    /**
     * @param string $type
     *
     * @throws TGException
     *
     * @return array|bool|string
     */
    private function readTypedField(string $type)
    {
        switch ($type) {
            // values of type # are serialized as 32-bit signed numbers from 0 to 2^31-1
            case '#':
            case 'int':
                return $this->readInt();
            case 'long':
                return $this->readLong();
            case 'int128':
                return $this->readInt128();
            case 'int256':
                return $this->readInt256();
            case 'bytes':
            case 'string':
                return $this->readString();
        }

        if (strstr($type, 'Vector')) {
            return $this->readVectorAsParam($type);
        }

        return $this->readObject();
    }

    /**
     * @param string $type
     *
     * @throws TGException
     *
     * @return array
     */
    private function readVectorAsParam(string $type)
    {
        preg_match('/.*<(.*)>.*/', $type, $matches);
        $type = $matches[1];

        $id = $this->readId();
        if ($id != 0x1cb5c415) {
            throw new TGException(TGException::ERR_DESERIALIZER_VECTOR_EXPECTED, 'vector expected! got: '.$id);
        }

        $length = $this->readInt();
        $objects = [];

        for ($i = 0; $i < $length; $i++) {
            $objects[] = $this->readTypedField($type);
        }

        return $objects;
    }

    /**
     * @throws TGException
     *
     * @return array
     */
    private function readVectorAsObject()
    {
        $length = $this->readInt();
        $objects = [];

        for ($i = 0; $i < $length; $i++) {
            $objects[] = $this->readObject();
        }

        return $objects;
    }

    /**
     * @throws TGException
     *
     * @return int
     */
    private function readId()
    {
        $a = $this->stream->read(4);
        $a = unpack('I', $a)[1];

        return $a;
    }

    /**
     * @throws TGException
     *
     * @return int
     */
    protected function readLong()
    {
        $value = $this->stream->read(8);
        $value = unpack('Q', $value)[1];

        return $value;
    }

    /**
     * @throws TGException
     *
     * @return int
     */
    private function readInt()
    {
        $a = $this->stream->read(4);
        $a = unpack('I', $a)[1];

        return $a;
    }

    /**
     * @throws TGException
     *
     * @return string
     */
    private function readInt128()
    {
        return $this->stream->read(16);
    }

    /**
     * @throws TGException
     *
     * @return string
     */
    private function readInt256()
    {
        return $this->stream->read(32);
    }

    /**
     * @throws TGException
     *
     * @return string
     */
    private function readString()
    {
        $lengthValue = $this->stream->read(1);
        $len = unpack('C', $lengthValue)[1];
        $padding = $this->posmod(-($len + 1), 4);

        if ($len == 254) {
            $lengthValue = $this->stream->read(3);
            $len = unpack('I', $lengthValue.pack('x'))[1];
            $padding = $this->posmod(-($len), 4);
        }

        $result = $len > 0 ? $this->stream->read($len) : '';
        $this->stream->read($padding);

        return $result;
    }

    /**
     * @param int $a
     * @param int $b
     *
     * @return float|int
     */
    private function posmod(int $a, int $b)
    {
        $resto = $a % $b;
        if ($resto < 0) {
            $resto += abs($b);
        }

        return $resto;
    }
}
