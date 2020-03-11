<?php

declare(strict_types=1);

namespace Helpers;

class PhpSerializationFixer
{
    /**
     * This method could be used for fixing serialized PHP objects when namespaces change.
     * If the serialized object is stored and then class signatures changes (namespace)
     * then deserialization will not work. This method helps to fix serialized messages.
     *
     * @param string $serialized Old serialized message.
     * @param string $from       Old namespace.
     * @param string $to         New namespace.
     *
     * @return string
     */
    public static function replaceNamespace(string $serialized, string $from, string $to): string
    {
        $fromLength = strlen($from);
        $toLength = strlen($to);
        $slashCount = substr_count($from, '\\\\');

        $replacerGen = function (string $prefix) use ($from, $to, $fromLength, $toLength, $slashCount): callable {
            return function (array $matches) use ($prefix, $from, $to, $fromLength, $toLength, $slashCount): string {
                $oldLength = (int) $matches[2];
                $newLength = $oldLength - $fromLength + $toLength + $slashCount;

                return "{$matches[1]}:{$newLength}:$prefix{$to}";
            };
        };

        $prefix3 = '"';
        $rx1 = '/(O):(\d+):'.$prefix3.'('.$from.')/';
        $serialized = preg_replace_callback($rx1, $replacerGen($prefix3), $serialized);
        $prefix3 = '"'."\1\1\1";
        $rx2 = '/(s):(\d+):'.$prefix3.'('.$from.')/';
        $search = '"'."\0";
        $serialized = str_replace($search, $prefix3, $serialized);
        $serialized = preg_replace_callback($rx2, $replacerGen($prefix3), $serialized);
        $serialized = str_replace($prefix3, $search, $serialized);

        return $serialized;
    }
}
