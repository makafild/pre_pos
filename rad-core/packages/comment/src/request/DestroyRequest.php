<?php

namespace Core\Packages\comment\src\request;

use Core\System\Http\Requests\FormRequestCustomize;
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
            'comment_ids'        => 'required|array',
            'comment_ids.*'        => 'required|exists:comments,id',
        ];
    }
}
