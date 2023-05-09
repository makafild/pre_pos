<?php

namespace App\Http\Requests\Customer\Customer;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class ScoreCustomerRequest
 *
 * @package App\Http\Requests\Customer\Customer
 * @property int $score
 */
class ScoreCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'score' => 'required|integer|min:1|max:5'
        ];
    }
}
