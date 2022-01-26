<?php

namespace App\Http\Requests\UserRequest;

use App\Rules\PasswordRules\ContainsLowerCase;
use App\Rules\PasswordRules\ContainsNumber;
use App\Rules\PasswordRules\ContainsUpperCase;
use Illuminate\Foundation\Http\FormRequest;

class UserRegisterRequest extends FormRequest
{
    const MIN_PASSWORD_LENGTH = 7;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // TODO: Add rfc, dns validation for email addresses
            'email' => 'required|unique:users|email',
            'password' => [
                'required',
                'string',
                'min:' . self::MIN_PASSWORD_LENGTH,
                new ContainsUpperCase,
                new ContainsLowerCase,
                new ContainsNumber,
            ],
            'first_name' => 'max:255',
            'last_name' => 'max:255',
            'add_to_newsletter' => 'boolean'
        ];
    }
}
