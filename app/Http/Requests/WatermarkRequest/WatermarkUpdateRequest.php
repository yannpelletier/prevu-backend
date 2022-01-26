<?php

namespace App\Http\Requests\WatermarkRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class WatermarkUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $watermark = $this->route()->parameter('watermark');
        return Gate::allows('update', $watermark);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'string',
            'dimension' => 'string',
            'position' => 'string',
        ];
    }
}
