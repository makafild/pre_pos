<?php

namespace Core\Packages\comment\src\request;

use Core\System\Http\Requests\FormRequestCustomize;
use Illuminate\Validation\Rule;

class ReplayRequest extends FormRequestCustomize
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'text' => 'required'
        ];
    }
}
