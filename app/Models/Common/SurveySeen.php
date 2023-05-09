<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class SurveySeen extends Model
{
	protected $fillable = [
		'user_id',
		'survey_id',
	];
	public $timestamps = false;

	public static function Check($userId, $companyId)
	{
		$companySurveys = Survey::CompanyId($companyId)
			->select('id')
			->get();

		$seenedSurveyIds = SurveySeen::where([
			'user_id' => $userId,
		])
			->get()
			->pluck('survey_id');

		return $companySurveys->whereNotIn('id', $seenedSurveyIds)->count();
	}
}
