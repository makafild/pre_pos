<?php

namespace App\Models\Billing;

use App\Models\Order\Order;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Charge
 *
 * @package App\Models\Billing
 * @property string  $id
 *
 * @property string  $amount
 * @property string  $payment
 * @property string  $method
 * @property string  $transaction_id
 * @property string  $status
 *
 * @property string  $user_id
 * @property User    $user
 *
 * @property string  $invoice_id
 * @property Invoice $invoice
 *
 * @property int     $order_id
 * @property Order   $order
 */
class Charge extends Model
{
	use SoftDeletes;

	const STATUS_PENDING = 'pending';
	const STATUS_GATEWAY = 'gateway';
	const STATUS_ERROR = 'error';
	const STATUS_DONE = 'done';
	const STATUS_UNDONE = 'undone';

	const STATUS = [
		self::STATUS_PENDING,
		self::STATUS_GATEWAY,
		self::STATUS_ERROR,
		self::STATUS_DONE,
		self::STATUS_UNDONE,
	];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['deleted_at'];


	public function User()
	{
		return $this->belongsTo(User::class);
	}

	public function Invoice()
	{
		return $this->belongsTo(User::class);
	}

	public function Order()
	{
		return $this->belongsTo(Order::class);
	}

}
