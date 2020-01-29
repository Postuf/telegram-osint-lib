<?php

namespace TelegramOSINT\TLMessage\TLMessage;

class Packer
{
    /**
     * @param int $value
     *
     * @return string
     */
    public static function packConstructor($value)
    {
        return self::packInt($value);
    }

    public static function packDouble(float $value)
    {
        return pack('e', $value);
    }

    /**
     * @param int $value
     *
     * @return string
     */
    public static function packInt($value)
    {
        return pack('I', $value);
    }

    /**
     * @param bool $value
     *
     * @return string
     */
    public static function packBool($value)
    {
        return self::packInt($value ? 0x997275b5 : 0xbc799737);
    }

    /**
     * @param int $value
     *
     * @return string
     */
    public static function packLongAsBytes($value)
    {
        return self::packString(pack('J', $value));
    }

    /**
     * @param int $value
     *
     * @return string
     */
    public static function packIntAsBytesLittleEndian($value)
    {
        return self::packString(strrev(self::packInt($value)));
    }

    /**
     * @param int $value
     *
     * @return string
     */
    public static function packLong($value) {
        return pack('Q', $value);
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public static function packBytes($value) {
        return $value;
    }

    /**
     * @param int $value
     *
     * @return string
     */
    public static function packString($value) {
        $l = strlen($value);
        if ($l <= 253) {
            $len = pack('C', $l);
            $padding = self::calcPadding($l + 1, 4);

        } else {
            $len = pack('C', 254).substr(pack('i', $l), 0, 3);
            $padding = self::calcPadding($l, 4);
        }

        return $len.$value.$padding;
    }

    /**
     * @param array    $array
     * @param callable $elementGeneratorCallback function(mixed)
     *
     * @return string
     */
    public static function packVector($array, $elementGeneratorCallback)
    {
        $vector =
            self::packInt(481674261).
            self::packInt(count($array));

        foreach ($array as $element)
            $vector .= $elementGeneratorCallback($element);

        return $vector;
    }

    /**
     * @param int $a
     * @param int $b
     *
     * @return string
     */
    private static function calcPadding(int $a, int $b)
    {
        $padLength = self::calcRemainder(-$a, $b);

        return str_pad('', $padLength, "\x00");
    }

    /**
     * @param int $a
     * @param int $b
     *
     * @return int
     */
    private static function calcRemainder(int $a, int $b)
    {
        $remainder = $a % $b;
        if ($remainder < 0)
            $remainder += abs($b);

        return $remainder;
    }
}
