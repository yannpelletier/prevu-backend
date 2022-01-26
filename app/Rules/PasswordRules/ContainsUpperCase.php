<?php

namespace App\Rules\PasswordRules;

use Illuminate\Contracts\Validation\Rule;

class ContainsUpperCase implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return preg_match ( '/[A-Z]/', $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.contains_uppercase');
    }
}
