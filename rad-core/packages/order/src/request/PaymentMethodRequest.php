<?php

namespace Core\Packages\order\src\request;

use App\Models\Product\Product;
use App\Rules\Jalali;
use Core\Packages\common\Constant;
use Core\System\Http\Requests\FormRequestCustomize;
use Illuminate\Validation\Rule;

class PaymentMethodRequest extends FormRequestCustomize
{
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
        $rulesValidation = [
            'company_id'        => 'nullable',
            'constant_fa' => 'required|unique:payment_methods',
            'constant_en' => 'required',
            'discount'       => 'nullable|numeric',
            'discount_max'   => 'nullable|numeric',
        ];

        if(!empty($this->route('id'))){
            $rulesValidation['constant_fa']='required|unique:payment_methods,constant_fa,'.$this->route('id');
        }
        return $rulesValidation;
    }
}
