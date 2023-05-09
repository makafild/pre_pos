<?php

namespace Core\Packages\news\src\request;
use App\Rules\Jalali;
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
            'title'       => 'required',
            'description' => 'required',

            'photo_id' => 'exists:files,id',

            'start_at' => [
//                new Jalali(),
            ],
            'end_at'   => [
//                new Jalali(),
            ],
        ];
    }
}
