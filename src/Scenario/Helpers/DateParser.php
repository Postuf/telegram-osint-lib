<?php

declare(strict_types=1);

namespace Scenario\Helpers;

class DateParser
{
    public static function parse(?string $date): ?int
    {
        if (!$date) {
            return null;
        }

        $fmt1 = 'YYYYmmdd';
        $fmt2 = 'YYYYmmdd HH:ii:ss';
        $fmt3 = 'YYYY-mm-dd HH:ii:ss';
        $dateFormatError = "invalid date format, use $fmt1|$fmt2|$fmt3";
        if (strlen($date) !== strlen($fmt1)
            && strlen($date) !== strlen($fmt2)
            && strlen($date) !== strlen($fmt3)) {
            throw new InvalidArgumentException($dateFormatError);
        }
        if (strlen($date) === strlen($fmt3)) {
            $parts = explode(' ', $date);
            if (count($parts) !== 2) {
                throw new InvalidArgumentException($dateFormatError);
            }
            $parts[0] = str_replace('-', '', $parts[0]);
            $date = implode(' ', $parts);
        }
        $y = substr($date, 0, 4);
        $m = substr($date, 4, 2);
        $d = substr($date, 6, 2);
        $his = '00:00:00';
        if (strlen($date) === strlen($fmt2)) {
            $parts = explode(' ', $date);
            if (count($parts) !== 2) {
                throw new InvalidArgumentException($dateFormatError);
            }
            $his = $parts[1];
        }

        return strtotime("$y-$m-$d $his");

    }
}
