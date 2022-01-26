<?php

namespace App\Http\Requests\WatermarkRequest;

use App\Watermark;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class WatermarkStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('store', Watermark::class);
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
                'mimes:png'
            ],
        ];
    }
}
