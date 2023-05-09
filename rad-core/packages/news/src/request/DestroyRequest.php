<?php

namespace Core\Packages\news\src\request;

use Core\Packages\common\Constant;
use Core\System\Http\Requests\FormRequestCustomize ;
use Core\System\Http\Traits\HelperRequestTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class DestroyRequest extends FormRequestCustomize
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
                Rule::exists('news')
                    ->where('id', $this->id),
            ],

        ];
    }

}
