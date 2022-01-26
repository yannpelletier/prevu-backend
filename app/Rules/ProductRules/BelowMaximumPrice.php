<?php


namespace App\Rules\ProductRules;


use Illuminate\Contracts\Validation\Rule;

class BelowMaximumPrice implements Rule
{
    private const MAXIMUM_PRICE_IN_CENTS = 1000000; // = 10 000 $

    /**
     * Determine if the price is above the minimum price limit.
     *
     * @param string $attribute
     * @param mixed $value The price value, in cents
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return $value <= self::MAXIMUM_PRICE_IN_CENTS;
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message()
    {
        return trans('validation.max.numeric', ['attribute' => 'price', 'max' => "$ " . self::MAXIMUM_PRICE_IN_CENTS / 100 . " USD"]);
    }
}
