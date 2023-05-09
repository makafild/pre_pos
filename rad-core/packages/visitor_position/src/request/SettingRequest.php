<?php

namespace Core\Packages\setting\src\request;

use Core\System\Http\Requests\FormRequestCustomize;
use Illuminate\Validation\Rule;

class SettingRequest extends FormRequestCustomize
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'setting' => 'required|array',
            'setting.*.id' => ['required',    Rule::exists('settings','id')],
            'setting.*.value' => ['required']
        ];
    }
}
