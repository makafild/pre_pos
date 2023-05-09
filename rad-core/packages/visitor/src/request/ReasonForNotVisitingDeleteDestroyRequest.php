<?php

namespace Core\Packages\visitor\src\request;

use Core\System\Http\Requests\FormRequestCustomize ;

class ReasonForNotVisitingDeleteDestroyRequest extends FormRequestCustomize
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => [
                'required',
                "array",
                "exists:reason_for_not_visitings,id"
            ],

        ];
    }

}
