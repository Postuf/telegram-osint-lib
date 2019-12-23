<?php

namespace Validators;


class ImportedPhoneValidator implements Validator
{

    /**
     * @param mixed $value
     * @return bool
     */
    public function validate($value): bool
    {
        $numberInt = (int)$value;
        return
            $numberInt &&
            $numberInt != 0 &&
            strlen(str_replace('+', '', $value)) == strlen((string)$numberInt);
    }

}