<?php

namespace Core\Packages\introducer_code\src\request;

use Core\System\Http\Requests\FormRequestCustomize;
use Illuminate\Validation\Rule;

class IntroducerCodeRequest extends FormRequestCustomize
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'code' => 'required',
            'title' => 'required',
            'company_id' => ['required',
                Rule::exists('users')
                    ->where('kind', 'company'),]
        ];
    }
}
