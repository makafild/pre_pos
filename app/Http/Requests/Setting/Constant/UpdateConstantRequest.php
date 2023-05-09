<?php

namespace App\Http\Requests\Setting\Constant;

use App\Models\Setting\Constant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UpdateConstantRequest
 *
 * @package App\Http\Requests\Setting\Constant
 * @property string $kind
 * @property string $constant_fa
 * @property string $constant_en
 */
class UpdateConstantRequest extends FormRequest
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
			'constant_en' => [
				'required',
				Rule::unique('constants', 'constant_en')
					->where('kind', $this->kind)
					->whereNot('id', $this->id),
			],
			'constant_fa' => [
				'required',
				Rule::unique('constants', 'constant_fa')
					->where('kind', $this->kind)
					->whereNot('id', $this->id),
			],
			'kind'        => [
				'required',
				'in:' . implode(',', Constant::CONSTANT_KINDS),
			],
		];
	}
}
