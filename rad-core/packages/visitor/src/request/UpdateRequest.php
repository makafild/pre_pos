<?php

namespace Core\Packages\visitor\src\request;

use Core\Packages\common\Constant;
use Core\System\Http\Requests\FormRequestCustomize;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequestCustomize
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'password' => [
                "confirmed",
                "min:1",
            ],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'mobile_number' => ['required',
                Rule::unique('users', 'mobile_number')
                    ->where('mobile_number', $this->mobile_number)
                    ->whereNot('id', $this->user_id)],
            'is_super_visitor' => 'required|boolean',
            'email' => ['nullable', 'string', 'email', 'max:255'],
            "visitors" => ['nullable','array', "exists:visitors,id"],
            "ref_id" => ["exists:visitors,id"],
            "user_id" => ['required', "exists:users,id"],
        ];
    }
}
