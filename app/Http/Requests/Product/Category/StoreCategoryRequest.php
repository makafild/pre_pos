<?php

namespace App\Http\Requests\Product\Category;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreCategoryRequest
 *
 * @package App\Http\Requests\Product\Category
 * @property string $title
 * @property int    $parent_id
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
			'title'     => 'required',
			'parent_id' => 'required|exists:categories,id',
		];
	}
}
