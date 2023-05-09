<?php

namespace Core\Packages\company\src\request;

use Core\System\Http\Requests\FormRequestCustomize ;

class ApproveStateRequest extends FormRequestCustomize
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_ids' =>'required|array',
            'user_ids.*' =>'required|integer|exists:users,id',
            'status' => 'required|in:1,0'
        ];
    }
}
