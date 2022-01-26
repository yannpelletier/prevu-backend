<?php

namespace App\Http\Requests\UserRequest;

use App\Rules\PasswordRules\ContainsLowerCase;
use App\Rules\PasswordRules\ContainsNumber;
use App\Rules\PasswordRules\ContainsUpperCase;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
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
            'old_password' => [
                'required_with:password',
                'filled',
                function ($attribute, $value, $fail) {
                    if (!Hash::check($value, Auth::user()->password)) {
                        $fail(trans('passwords.no_match'));
                    }
                },
            ],
            'password' => [
                'nullable',
                'filled',
                'string',
                'min:' . self::MIN_PASSWORD_LENGTH,
                new ContainsUpperCase,
                new ContainsLowerCase,
                new ContainsNumber,
            ],
            'password_confirmation' => 'required_with:password|filled|same:password',
            'send_sale_notifications' => 'nullable|boolean',
            'send_ip_notifications' => 'nullable|boolean',
            'analytics_currency' => 'nullable|string',
            'sale_currency' => 'nullable|string',
            'first_time_login' => ['boolean', Rule::in(false)]
        ];
    }

}
