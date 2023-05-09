<?php

namespace Core\Packages\shop;

use Core\Packages\user\Users;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class TourVisitors
 *
 * @package App\Models\User
 *
 * @property string $title
 * @property string $code
 *
 * @property int    $company_id
 * @property Users   $company
 */
class IntroducerCode extends Model
{
	use SoftDeletes;

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
}
