<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage;

use function pack;
use function str_pad;
use function strrev;
use function substr;

class Packer
{
    /**
     * @param int $value
     *
     * @return string
     */
    public static function packConstructor(int $value): string
    {
        return self::packInt($value);
    }

    /**
     * @param float $value
     *
     * @return string
     */
    public static function packDouble(float $value): string
    {
        return pack('e', $value);
    }

    /**
     * @param int $value
     *
     * @return string
     */
    public static function packInt(int $value): string
    {
        return pack('I', $value);
    }

    /**
     * @param bool $value
     *
     * @return string
     */
    public static function packBool(bool $value): string
    {
        return self::packInt($value ? 0x997275b5 : 0xbc799737);
    }

    /**
     * @param int $value
     *
     * @return string
     */
    public static function packLongAsBytes(int $value): string
    {
        return self::packString(pack('J', $value));
    }

    /**
     * @param int $value
     *
     * @return string
     */
    public static function packIntAsBytesLittleEndian(int $value): string
    {
        return self::packString(strrev(self::packInt($value)));
    }

    /**
     * @param int $value
     *
     * @return string
     */
    public static function packLong(int $value): string {
        return pack('Q', $value);
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public static function packBytes(string $value): string {
        return $value;
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public static function packString(string $value): string {
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
    public static function packVector(array $array, callable $elementGeneratorCallback): string
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
    private static function calcPadding(int $a, int $b): string
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
    private static function calcRemainder(int $a, int $b): int
    {
        $remainder = $a % $b;
        if ($remainder < 0)
            $remainder += abs($b);

        return $remainder;
    }
}
