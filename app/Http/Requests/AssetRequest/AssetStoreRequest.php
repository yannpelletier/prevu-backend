<?php

namespace App\Http\Requests\AssetRequest;

use App\Asset;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Http\FormRequest;

class AssetStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('store', Asset::class);
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
                'mimes:jpg,png,jpeg,'
            ],
        ];
    }
}
