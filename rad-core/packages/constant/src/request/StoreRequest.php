<?php

namespace Core\Packages\constant\src\request;

use Core\Packages\common\Constant;
use Core\System\Http\Requests\FormRequestCustomize;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequestCustomize
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        return [
            'constant_en' => [
                'required',
                Rule::unique('constants', 'company_id')->where(function ($query) {
                    return $query
                        ->where('company_id', auth('api')->id())
                        ->whereNull('deleted_at')
                        ->where('kind', $this->kind);
                }),
            ],
            'constant_fa' => [
                'required',
                Rule::unique('constants')->where(function ($query) {
                    return $query
                        ->where('company_id', auth('api')->id())
                        ->whereNull('deleted_at')
                        ->where('kind', $this->kind);
                }),
            ],
            'kind' => [
                'required',
                'in:' . implode(',', Constant::CONSTANT_KINDS),
            ],
            'percent' => [
				'required_if:kind,' . Constant::TAX,
            ],


        ];
    }
}
