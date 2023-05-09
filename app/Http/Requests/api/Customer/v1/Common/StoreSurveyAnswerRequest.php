<?php

namespace App\Http\Requests\api\Customer\v1\Common;

use App\Models\Common\Survey;
use App\Models\User\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreSurveyAnswerRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		$cities = auth('mobile')->user()->Cities->pluck('id')->all();
		/** @var Survey $survey */
		$survey = Survey::findOrFail($this->route('id'));

		$company = User::where('kind', User::KIND_COMPANY)
			->where('id', $survey->company_id)
			->whereHas('Cities', function ($query) use ($cities) {
				$query->whereIn('id', $cities);
			})->first();

		if ($company)
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
			'questions'           => 'required|array',
			'questions.*.id'      => 'nullable|exists:survey_questions,id',
			'questions.*.options' => 'required|array',
		];
	}

	public function messages()
	{
		$messages = [];
		foreach ($this->questions as $key => $question) {
			$messages["questions.{$key}.options.required"] = trans('validation.survey_questions_option_required', ['index' => $key + 1]);
		}

		return $messages;
	}
}
