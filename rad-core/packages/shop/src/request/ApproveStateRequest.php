<?php

namespace Core\Packages\customer\src\request;

use Core\Packages\common\Constant;
use Core\System\Http\Requests\FormRequestCustomize ;
use Illuminate\Validation\Rule;

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
