<?php

namespace App\Http\Requests\api\Customer\v1\Common;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class DeleteCompanyFavoriteRequest
 *
 * @package App\Http\Requests\api\Customer\v1\Common
 */
class DeleteCompanyFavoriteRequest extends FormRequest
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
			'company_id' => 'required|integer|exists:users,id'
		];
    }
}
