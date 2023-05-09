<?php

namespace App\Http\Requests\api\Company\v1\Category;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreCategoryRequest
 *
 * @package App\Http\Requests\api\Company\v1\Category
 * @property int    $parent_id
 * @property string $title
 */
class StoreCategoryRequest extends FormRequest
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
			//
		];
	}
}
