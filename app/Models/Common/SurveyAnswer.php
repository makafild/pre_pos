<?php

namespace App\Models\Common;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SurveyAnswer
 *
 * @package App\Models\Common
 * @property int    $question_id
 * @property Survey $survey
 * @property int    $user_id
 * @property User   $User
 * @property mixed  $questions
 */
class SurveyAnswer extends Model
{
	protected $fillable = [
		'survey_id',
		'user_id',
		'answer',
	];

	protected $casts = [
		'questions' => 'array',
	];

	public function Survey()
	{
		return $this->belongsTo(Survey::class, 'survey_id', 'id');
	}

	public function User()
	{
		return $this->belongsTo(User::class)->withTrashed();
	}

	public function getQuestionsAttribute($answers)
	{
		$answers = json_decode($answers);

		foreach ($this->survey->questions as $question) {

			// image kind
			if ($question->kind == SurveyQuestion::KIND_IMAGE) {
				foreach ($answers as &$answer) {
					if ($answer->id == $question->id) {
						$answer->options[0] = File::find($answer->options[0]);
					}
				}
			}
		}

		return $answers;
	}
}
