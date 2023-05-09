<?php

namespace Core\Packages\visitor\src\request;
use Core\Packages\common\Constant;
use Core\System\Http\Requests\FormRequestCustomize ;
use Illuminate\Validation\Rule;

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
            'password' => [
                'required',
                "confirmed",
                "min:1",
            ],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'mobile_number' => 'required|unique:users',
            'is_super_visitor' => 'required|boolean',
            'email' => ['nullable', 'string', 'email', 'max:255'],
            "visitors"=>['array',   "exists:visitors,id"],
            "ref_id"=>[   "exists:visitors,id"]

        ];
    }
}
