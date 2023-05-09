<?php

namespace Core\Packages\constant\src\request;

use Core\Packages\common\Constant;
use Core\System\Http\Requests\FormRequestCustomize;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequestCustomize
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
                Rule::unique('constants', 'constant_en')
                    ->where('kind', $this->kind)
                    ->whereNull('deleted_at')
                    ->whereNot('id', $this->id),
            ],
            'constant_fa' => [
                'required',
                Rule::unique('constants', 'constant_fa')
                    ->where('kind', $this->kind)
                    ->whereNull('deleted_at')
                    ->whereNot('id', $this->id),
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
