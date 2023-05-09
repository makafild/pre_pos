<?php

namespace Core\Packages\order\src\request;

use App\Models\Order\Order;
use App\Rules\CheckOrderReferralIds;
use App\Rules\CheckRowVersion;
use App\Rules\CheckOrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Core\Packages\common\Constant;


/**
 * Class ChangeOrderStatusRequest
 *
 * @package App\Http\Requests\Order\Order
 * @property string $status
 */
class ChangeOrderStatusRequest extends FormRequest
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

        $rules = [];

        $rules['orders'] = [
            'required',
            'array',
            new CheckRowVersion('orders'),
            new CheckOrderStatus(),
        ];
        $rules['orders.*.id'] = [
            'required',
            Rule::exists('orders', 'id')
                ->where('status', 'registered'),
              //  new CheckOrderReferralIds($this->status['name']),
        ];
        $rules['orders.*.row_version'] = [
            'required',
            Rule::exists('orders'),
        ];
        $rules['status.name'] = 'required|in:confirmed,rejected';

        $rules['reject_text_id'] = ['nullable',
            Rule::exists('constants', 'id')
                ->where('kind', Constant::ORDER_REJECT_TEXT)
        ];


        return $rules;
	}
}
