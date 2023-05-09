<?php

namespace Core\Packages\group\src\request;

use Core\Packages\group\Group;
use Core\System\Http\Requests\FormRequestCustomize;

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

            //'salt' => ['required','min:3','unique:groups'],
            'name' => ['required','min:3'],
            'for' => ['required', 'in:' . implode(',', Group::FOR_KINDS)],
            'access' => 'required|array',


        ];
    }
}
