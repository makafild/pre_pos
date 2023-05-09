<?php

namespace Core\Packages\constant\src\request;

use Core\Packages\common\Constant;
use Core\System\Http\Requests\FormRequestCustomize;
use Core\System\Http\Traits\HelperRequestTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class CompanyRequest extends FormRequestCustomize
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'kind' => [
                'required',
                'in:' . implode(',', Constant::CONSTANT_KINDS),

            ],
            'company_id' => ['required', 'integer', 'exists:users,id']

        ];
    }

}
