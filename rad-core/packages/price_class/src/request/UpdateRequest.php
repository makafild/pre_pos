<?php

namespace Core\Packages\price_class\src\request;


use Core\Packages\common\Constant;
use Core\System\Http\Requests\FormRequestCustomize ;
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
            'title' => [
                'required',
                Rule::unique('price_classes', 'title')
                    ->where('title', $this->kind)
                    ->whereNot('id', $this->id),
            ],
        ];
    }
}
