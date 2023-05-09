<?php

namespace Core\Packages\order\src\request;

use App\Models\Product\Product;
use App\Rules\Jalali;
use Core\Packages\common\Constant;
use Core\System\Http\Requests\FormRequestCustomize;
use Illuminate\Validation\Rule;

class PaymentMethodDefaultRequest extends FormRequestCustomize
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
            'status' => 'required|boolean'
        ];

        return $rulesValidation;
    }
}