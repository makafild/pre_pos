<?php

namespace Core\Packages\customer\src\request;

use Core\Packages\common\Constant;
use Core\System\Http\Requests\FormRequestCustomize;
use Illuminate\Validation\Rule;

class AddCustomerToRouterRequest extends FormRequestCustomize
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [

            'routes_id' => 'required|integer|exists:routes,id',
            'customer_id' => 'required|array',
        ];
    }
}
