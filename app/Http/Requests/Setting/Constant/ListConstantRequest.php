<?php

namespace App\Http\Requests\Setting\Constant;

use App\Models\Setting\Constant;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class ListConstantRequest
 *
 * @package App\Http\Requests\Setting\Constant
 * @property string $kind
 */
class ListConstantRequest extends FormRequest
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
			'kind' => [
				'required',
				'in:' . implode(',', Constant::CONSTANT_KINDS),
			],
		];
	}
}
