<?php

namespace App\Http\Requests\ProductRequest;

use App\Product;
use App\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ProductStoreRequest extends FormRequest
{
    private const MIN_HEIGHT = 5;
    private const MAX_HEIGHT = 10000;
    private const MIN_WIDTH = 5;
    private const MAX_WIDTH = 10000;
    private const MAX_SIZE_KILO_BYTES = 100000;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @param User $user
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('store', Product::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'file' => [
                'required',
                'file',
                Product::getMimesRule(),
                'max:' . self::MAX_SIZE_KILO_BYTES,
                Rule::dimensions()
                    ->maxHeight(self::MAX_HEIGHT)
                    ->minHeight(self::MIN_HEIGHT)
                    ->maxWidth(self::MAX_WIDTH)
                    ->minWidth(self::MIN_WIDTH),
            ],
        ];
    }
}
