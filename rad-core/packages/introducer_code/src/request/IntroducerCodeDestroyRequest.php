<?php

namespace Core\Packages\introducer_code\src\request;

use Core\System\Http\Requests\FormRequestCustomize;

class IntroducerCodeDestroyRequest extends FormRequestCustomize
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'ids' =>'required|array',
            'ids.*' =>'required|exists:introducer_codes,id',
        ];
    }
}
