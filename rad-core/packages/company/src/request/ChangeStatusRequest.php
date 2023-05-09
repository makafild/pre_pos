<?php

namespace Core\Packages\company\src\request;

use Core\Packages\user\Users;
use Core\System\Http\Requests\FormRequestCustomize ;
use Illuminate\Validation\Rule;
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
            'status' => 'required|in:' . implode(',', Users::STATUS),
            'id' => [
                'required',
                Rule::exists('users')
                    ->where('id', $this->id),
            ],
        ];
    }
}
