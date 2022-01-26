<?php

namespace App\Http\Requests\UserRequest;

use App\Rules\PasswordRules\ContainsLowerCase;
use App\Rules\PasswordRules\ContainsNumber;
use App\Rules\PasswordRules\ContainsUpperCase;
use Illuminate\Foundation\Http\FormRequest;

class UserResetPasswordRequest extends FormRequest
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
            'token' => 'required|exists:users,password_reset_token',
            'password' => [
                'required',
                'filled',
                'string',
                'min:' . self::MIN_PASSWORD_LENGTH,
                new ContainsUpperCase,
                new ContainsLowerCase,
                new ContainsNumber,
            ],
            'password_confirmation' => 'required|filled|same:password',
        ];
    }
}
