<?php


namespace App\Rules\ProductRules;


use Illuminate\Contracts\Validation\Rule;

class AboveMinimumPrice implements Rule
{
    private const MINIMUM_PRICE_IN_CENTS = 200;

    /**
     * Determine if the price is above the minimum price limit.
     *
     * @param string $attribute
     * @param mixed $value The price value, in cents
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return $value >= self::MINIMUM_PRICE_IN_CENTS;
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message()
    {
        return trans('validation.min.numeric', ['attribute' => 'price', 'min' => "$ " . self::MINIMUM_PRICE_IN_CENTS / 100 . " USD"]);
    }
}
