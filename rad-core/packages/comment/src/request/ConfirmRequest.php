<?php

namespace Core\Packages\comment\src\request;

use Core\System\Http\Requests\FormRequestCustomize;
use Illuminate\Validation\Rule;

class ConfirmRequest extends FormRequestCustomize
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'comment_ids' => 'required|array',
            'comment_ids.*' => ['required', Rule::exists('comments', 'id')],
            'status' => ['required','in:1,0']
        ];
    }
}
