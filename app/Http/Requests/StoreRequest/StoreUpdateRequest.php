<?php

namespace App\Http\Requests\StoreRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreUpdateRequest extends FormRequest
{
    private const CURRENCIES = ['USD'];

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $store = $this->route()->parameter('store');
        return Gate::allows('update', $store);
    }

    /**
     * Get the validation rules that apply to the request.
     * The slug must start with a letter and continue with
     * letters, numbers, slashes and underscores.
     *
     * @return array
     */
    public function rules()
    {
        $store = $this->route()->parameter('store');
        $rootSections = $this->get('root_sections');
        return array_merge($store->getRootSectionsRules($rootSections), [
            'currency' => Rule::in(self::CURRENCIES),
            'slug' => [
                'required',
                'sometimes',
                'string',
                'min:3',
                'max:80',
                'regex:/^[a-zA-Z]+[a-zA-Z0-9-_]+$/',
                Rule::unique('stores', 'slug')->ignore($store->slug, 'slug'),
            ],
        ]);
    }
}
