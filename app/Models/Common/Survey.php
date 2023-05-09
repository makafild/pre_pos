<?php

namespace App\Models\Common;

use App\Models\User\User;
use App\Traits\VersionObserve;
use Core\Packages\gis\Province;
use Core\Packages\gis\City;
use Core\Packages\gis\Areas;
use Core\Packages\gis\Routes;
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
	protected $fillable = [
	    'type',
	    'title',
	    'from_date',
	    'to_date'
    ];


	public function Company()
	{
		return $this->belongsTo(User::class)->withTrashed();
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
		return $query->where('company_id', auth()->id());
	}

    public function Provinces()
    {
        return $this->belongsToMany(Province::class, 'survey_province', 'survey_id');
    }

    public function Cities()
    {
        return $this->belongsToMany(City::class, 'survey_city', 'survey_id');
    }

    public function Areas()
    {
        return $this->belongsToMany(Areas::class, 'survey_area', 'survey_id', "area_id");
    }

    public function Routes()
    {
        return $this->belongsToMany(Routes::class, 'survey_route', 'survey_id', "route_id");
    }

    public function Customers()
    {
        return $this->belongsToMany(User::class, 'survey_customer', 'survey_id', "customer_id");
    }

    public function scopeCompanyId($query, $id)
	{
		return $query->where('company_id', $id);
	}
}
