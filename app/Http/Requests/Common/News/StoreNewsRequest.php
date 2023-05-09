<?php

namespace App\Http\Requests\Common\News;

use App\Rules\Jalali;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreNewsRequest
 *
 * @package App\Http\Requests\Common\News
 *
 * @property string $title
 * @property string $description
 *
 * @property int    $photo_id
 *
 * @property string $start_at
 * @property string $end_at
 */
class StoreNewsRequest extends FormRequest
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
			'title'       => 'required',
			'description' => 'required',

			'photo_id' => 'exists:files,id',

			'start_at' => [
				new Jalali(),
			],
			'end_at'   => [
				new Jalali(),
			],
		];
	}
}
