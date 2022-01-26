<?php

namespace App\Http\Requests\ProductRequest;

use App\Rules\ProductRules\AboveMinimumPrice;
use App\Rules\ProductRules\BelowMaximumPrice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ProductUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $product = $this->route()->parameter('product');
        return Gate::allows('update', $product);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $product = $this->route()->parameter('product');

        return array_merge($product->getFilterRules('filters'), [
            'price' => [
                'integer',
                new AboveMinimumPrice,
                new BelowMaximumPrice
            ],
            'name' => 'string|sometimes|required|min:3|max:80',
            'description' => 'string|max:1000',
            'slug' => [
                'string',
                'min:3',
                'max:80',
                'regex:/^[a-zA-Z0-9-_]+$/',
                Rule::unique('products')->where('user_id', Auth::user()->id)->whereNot('id', $product->id),
            ],
            'thumbnail_type' => 'string',
            'custom_thumbnail_id' => [
                'sometimes',
                'nullable',
                'asset',
            ],
        ]);
    }
}
