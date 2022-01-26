<?php

namespace App\Http\Requests\PurchaseRequest;

use App\Purchase;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class PurchaseStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('store', Purchase::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'product_ids' => 'required|array',
            'card_token' => 'required|string|min:1'
        ];
    }
}
