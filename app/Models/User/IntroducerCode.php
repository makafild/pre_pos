<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class IntroducerCode
 *
 * @package App\Models\User
 *
 * @property string $title
 * @property string $code
 *
 * @property int    $company_id
 * @property User   $company
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
		return $this->belongsTo(User::class)->withTrashed();
	}
}
