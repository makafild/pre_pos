<?php

namespace App\Models\Common;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Suggestion
 *
 * @package App\Models\Common
 * @property string $suggestion
 *
 * @property int    $company_id
 * @property User   $company
 *
 * @property int    $user_id
 * @property User   $user
 *
 * @method static Suggestion CompanyId(integer $company_id)
 * @method static Suggestion UserId(integer $company_id)
 */
class Suggestion extends Model
{
	use SoftDeletes;

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['deleted_at'];



	protected $fillable = [
		'suggestion',
		'company_id',
		'user_id',
	];

	public function Company()
	{
		return $this->belongsTo(User::class)->withTrashed();
	}

	public function User()
	{
		return $this->belongsTo(User::class);
	}

	// ********************************* Scope *********************************

	public function scopeCompanyId($query, $company_id = NULL)
	{
		if ($company_id) {
			return $query->where('company_id', $company_id);
		}

		return $query;
	}

	public function scopeUserId($query, $user_id = NULL)
	{
		if ($user_id) {
			return $query->where('user_id', $user_id);
		}

		return $query;
	}
}
