<?php

namespace Core\Packages\news\src\request;

use Core\Packages\common\Constant;
use Core\System\Http\Requests\FormRequestCustomize ;
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
            'title'       => 'required',
            'description' => 'required',

            'photo_id' => 'exists:files,id',

            'start_at' => [

            ],
            'end_at' => [
            ],


            'id' => ['required', Rule::exists('news')->where('id', $this->id),],
        ];
    }
}
