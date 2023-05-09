<?php

namespace App\Http\Requests\Common\Survey;

use App\Models\Common\SurveyQuestion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UpdateSurveyRequest
 *
 * @package App\Http\Requests\Common\Survey
 *
 * @property string $title
 * @property array  $questions
 */
class UpdateSurveyRequest extends FormRequest
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
			'title'                => 'required',
			'questions'            => 'required|array',
			'questions.*.id'       => [
				'nullable',
				Rule::exists('survey_questions', 'id'),
			],
			'questions.*.kind'     => [
				'required',
				'in:' . implode(',', SurveyQuestion::KINDS),
			],
			'questions.*.question' => 'required',
			'questions.*.options'  => 'required|array',
			'row_version'          => ['required', Rule::exists('surveys')->where('id', $this->id),],
		];
	}
}
