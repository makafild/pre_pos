<?php

namespace Core\Packages\company\request;

use App\Rules\CheckRowVersion;
use Core\System\Http\Requests\FormRequestCustomize ;

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
            'models' => [
                'required',
                'array',
                new CheckRowVersion('users'),
            ],

        ];
    }

}
