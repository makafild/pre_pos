<?php

namespace App\Models\Billing;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Invoice
 *
 * @package App\Models\Billing
 * @property int    $id
 *
 * @property string $kind
 * @property int    $amount
 * @property string $title
 * @property string $description
 * @property string $status
 *
 * @property int    $user_id
 * @property User   $user
 *
 * @method static Invoice UserId(integer $userId)
 */
class Invoice extends Model
{

	use SoftDeletes;

	const KIND_CHARGE = 'charge';

	const STATUS_STORE = 'store';
	const STATUS_CONFIRM = 'confirm';
	const STATUS_UNDONE = 'undone';
	const STATUS_DONE = 'done';

	const STATUS = [
		self::STATUS_STORE,
		self::STATUS_CONFIRM,
		self::STATUS_DONE,
		self::STATUS_UNDONE,
	];


	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['deleted_at'];

	protected $appends = [
		'status_translate',
	];

	public function User()
	{
		return $this->belongsTo(User::class);
	}


	public function scopeUserId($query, $userId)
	{
		if ($userId)
			return $query->where('user_id', $userId);

		return $query;
	}

	public function getStatusTranslateAttribute()
	{
		return trans("translate.billing.invoice.{$this->status}");
	}
}
