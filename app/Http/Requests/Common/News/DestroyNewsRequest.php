<?php

namespace App\Http\Requests\Common\News;

use App\Rules\CheckRowVersion;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class DestroyNewsRequest
 *
 * @package App\Http\Requests\Common\News
 * @property array $news
 */
class DestroyNewsRequest extends FormRequest
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
			'news' => [
				'required',
				'array',
				new CheckRowVersion('news'),
			],
		];
	}
}
