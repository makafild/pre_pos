<?php

namespace App\Http\Requests\api\Customer\v1\Common;

use App\Models\User\User;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class SuggestionRequest
 *
 * @package App\Http\Requests\api\Customer\v1\Common
 * @property string suggestion
 * @property int company_id
 */
class StoreSuggestionRequest extends FormRequest
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
			->where('id', $this->company_id)
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
			'suggestion' => 'required|min:5|max:200',
			'company_id' => 'required|integer|exists:users,id',
		];
	}
}
