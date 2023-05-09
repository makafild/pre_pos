<?php

namespace App\Http\Requests\api\Customer\v1\Common;

use App\Models\User\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class StoreMessageRequest
 *
 * @package App\Http\Requests\api\Customer\v1\Common
 *
 * @property integer $to_id
 * @property integer $message
 */
class StoreMessageRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		$cities = auth('mobile')->user()->Cities->pluck('id')->all();

		$company = User::where('kind', User::KIND_COMPANY)
			->where('id', $this->to_id)
			->whereHas('Cities', function ($query) use ($cities) {
				$query->whereIn('id', $cities);
			})->first();

		if ($company)
			return true;

		return false;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
			'to_id'   => [
				'required',
				Rule::exists('users', 'id')
					->where('kind', User::KIND_COMPANY),
			],
			'message' => 'required|string|min:1|max:255',
		];
	}
}
