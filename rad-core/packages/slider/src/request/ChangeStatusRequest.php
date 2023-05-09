<?php

namespace Core\Packages\slider\src\request;

use Illuminate\Validation\Rule;
use Core\Packages\slider\Slider;
use Core\System\Http\Requests\FormRequestCustomize;

class ChangeStatusRequest extends FormRequestCustomize
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'value' => 'required|in:' . implode(',', Slider::STATUS),
            'id' => [
                'required',
                Rule::exists('sliders')
                    ->where('id', $this->id),
            ],
        ];
    }
}
