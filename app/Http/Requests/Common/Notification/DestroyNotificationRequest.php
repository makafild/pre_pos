<?php

namespace App\Http\Requests\Common\Notification;

use App\Rules\CheckRowVersion;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class DestroyNewsRequest
 *
 * @package App\Http\Requests\Common\News
 * @property array $models
 */
class DestroyNotificationRequest extends FormRequest
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
		/*//	'models' => [
				'required',
				'array',
				new CheckRowVersion('notifications'),
			]*/
		];
	}
}
