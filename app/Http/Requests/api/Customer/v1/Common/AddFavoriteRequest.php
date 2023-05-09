<?php

namespace App\Http\Requests\api\Customer\v1\Common;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class AddFavoriteRequest
 *
 * @package App\Http\Requests\api\Customer\v1\Common
 * @property integer $product_id
 */
class AddFavoriteRequest extends FormRequest
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
        	'product_id' => 'required|integer|exists:products,id'
        ];
    }
}
