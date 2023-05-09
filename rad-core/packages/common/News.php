<?php

namespace Core\Packages\common;

use App\Models\User\User;
use App\Traits\VersionObserve;
use Carbon\Carbon;
use Hekmatinasser\Verta\Verta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class News
 *
 * @package App\Models\Common
 * @property int    $id
 * @property string $title
 * @property string $description
 * @property string $video_url
 *
 * @property int    $photo_id
 * @property File   $photo
 *
 * @property int    $company_id
 * @property User   $company
 *
 * @property int    $creator_id
 * @property User   $creator
 *
 * @property Carbon $start_at
 * @property Carbon $end_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @method static News CompanyId(integer $company_id)
 * @method static Slider Active()
 */
class News extends Model
{
	use VersionObserve, SoftDeletes;

	const STATUS_ACTIVE = 'active';
	const STATUS_INACTIVE = 'inactive';

	const STATUS = [
		self::STATUS_ACTIVE,
		self::STATUS_INACTIVE,
	];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = [
		'end_at',
		'deleted_at',
	];

	public function Photo()
	{
		return $this->belongsTo(File::class);
	}

	public function Company()
	{
		return $this->belongsTo(User::class)->withTrashed();
	}

	public function Creator()
	{
		return $this->belongsTo(User::class);
	}

	// ********************************* Scope *********************************

	public function scopeCompanyId($query, $company_id = NULL)
	{
		if ($company_id) {
			return $query->where('company_id', $company_id);
		}

		return $query->whereNull('company_id');
	}

	// ********************************* Attributes *********************************

	public function getStartAtAttribute()
	{
		$v = new Verta($this->attributes['start_at']);

		return str_replace('-', '/', $v->formatDate());
	}

	public function getEndAtAttribute()
	{
		$v = new Verta($this->attributes['end_at']);

		return str_replace('-', '/', $v->formatDate());
	}

	// ********************************* Scope *********************************

	public function scopeActive($query)
	{
		return $query->whereDate('start_at', '<=', Carbon::now())
			->whereDate('end_at', '>=', Carbon::now())
			->where('status', self::STATUS_ACTIVE);
	}
}
