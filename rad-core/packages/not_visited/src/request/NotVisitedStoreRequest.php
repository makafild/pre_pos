<?php

namespace Core\Packages\not_visited\src\request;

use Core\System\Http\Requests\FormRequestCustomize;

class NotVisitedStoreRequest extends FormRequestCustomize
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'message' => 'required',
        ];
    }
}
