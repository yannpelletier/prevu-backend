<?php

namespace App\Http\Requests\ProductRequest;

use App\User;
use App\Product;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Http\FormRequest;

class ProductDestroyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @param User $user
     * @param Product $product
     * @return bool
     */
    public function authorize()
    {
        $product = $this->route()->parameter('product');
        return Gate::allows('destroy', $product);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }
}
