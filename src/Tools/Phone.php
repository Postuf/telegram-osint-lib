<?php

namespace Tools;

class Phone
{
    /**
     * Phones can be null because user could hide his phone
     *
     * @param null|string $phone
     *
     * @return string
     */
    public static function convertToTelegramView(?string $phone)
    {
        // remove all non-digits
        $digital = preg_replace("/\D/u", '', $phone);

        return (string) (int) $digital;
    }

    /**
     * Phones can be null because user could hide his phone
     *
     * @param null|string $phone1
     * @param null|string $phone2
     *
     * @return bool
     */
    public static function equal(?string $phone1, ?string $phone2)
    {
        $code = (!$phone1 && !$phone2)
            ? -1
            : strcmp(
                self::convertToTelegramView($phone1),
                self::convertToTelegramView($phone2)
            );

        return $code == 0;
    }
}
