<?php

namespace Core\Packages\shop;

use App\Traits\VersionObserve;
use Core\Packages\user\Users;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Survey
 *
 * @package App\Models\Common
 * @property int              $id
 * @property string           $title
 * @property int              $company_id
 * @property User             $company
 * @property SurveyQuestion[] $questions
 * @property SurveyAnswer     $answers
 * @method static Survey CompanyId(int $company_id)
 */
class Survey extends Model
{
	use VersionObserve, SoftDeletes;

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['deleted_at'];


	public function Company()
	{
		return $this->belongsTo(Users::class)->withTrashed();
	}

	public function Questions()
	{
		return $this->hasMany(SurveyQuestion::class);
	}

	public function Answers()
	{
		return $this->hasMany(SurveyAnswer::class);
	}

	public function scopeMine($query)
	{
		return $query->where('company_id', auth('api')->id());
	}

	public function scopeCompanyId($query, $id)
	{
		return $query->where('company_id', $id);
	}
}
