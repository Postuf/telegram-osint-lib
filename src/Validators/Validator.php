<?php

namespace TelegramOSINT\Validators;

interface Validator
{
    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function validate($value): bool;
}
