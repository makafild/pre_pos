<?php

namespace Core\Packages\order\src\request;

use App\Models\Product\Product;
use App\Rules\Jalali;
use Core\Packages\common\Constant;
use Core\System\Http\Requests\FormRequestCustomize;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequestCustomize
{
    /**
     * @var Product[]
     */
    private $productEntities;

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

        $rules = [
            'date_of_sending' => [
                'required',
//                new Jalali(),
            ],
            'customer_id' => "required|exists:users,id",
            'company_id' => "nullable|exists:users,id",
            'visitor_id' => "nullable|exists:visitors,id",
            'coupons' => [
                'array',
                Rule::exists('coupons', 'coupon'),
            ],
            'description' => 'nullable',
            'payment_confirm' => 'nullable',
            'transfer_number' => 'nullable',
            'carriage_fares' => 'nullable',
            'registered_source' => "nullable"
        ];
        return  $rules;
    }

}
