<?php

namespace App\Http\Requests\Order\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UpdateOrderRequest
 *
 * @package App\Http\Requests\Order\Order
 * @property object $additions
 */
class UpdateOrderRequest extends FormRequest
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
			'additions'         => 'array',
			'additions.*.id'    => 'nullable|exists:additions,id',
			'additions.*.key'   => 'required',
			'additions.*.value' => 'required',

			'row_version' => ['required', Rule::exists('orders')->where('id', $this->id),],
		];
	}

	public function messages()
	{
		$messages = [];
		foreach ($this->additions as $key => $question) {
			$messages["additions.{$key}.id.exists"] = trans('validation.order_additions_id_exists', ['index' => $key + 1]);
			$messages["additions.{$key}.key.required"] = trans('validation.order_additions_key_required', ['index' => $key + 1]);
			$messages["additions.{$key}.value.required"] = trans('validation.order_additions_value_required', ['index' => $key + 1]);
		}

		return $messages;
	}
}
