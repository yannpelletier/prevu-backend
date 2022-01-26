<?php

namespace App\Http\Requests\PurchaseRequest;

use App\Purchase;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class PurchaseZipRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $purchaseIds = $this->get('ids');
        $purchases = Purchase::findMany($purchaseIds);
        foreach($purchases as $purchase){
            if(!Gate::allows('show-original', $purchase)){
                return false;
            }
        }
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
            //
        ];
    }
}
