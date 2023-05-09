<?php

namespace App\Common;

use App\Models\User\User;
use Carbon\Carbon;
use Hekmatinasser\Verta\Verta;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CompanyReport
 *
 * @package App\Common
 * @property integer $id
 *
 * @property array   $turn_overs
 * @property array   $account_balances
 * @property array   $factors
 * @property array   $return_cheques
 *
 * @property integer $customer_id
 * @property User    $customer
 *
 * @property integer $company_id
 * @property User    $company
 *
 * @property User[]  $companies
 *
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 *
 * @method static CompanyReport CustomerId(integer $customerId)
 */
class CompanyReport extends Model
{
	protected $casts = [
		'turn_overs'       => 'array',
		'account_balances' => 'array',
		'factors'          => 'array',
		'return_cheques'   => 'array',
	];

	protected $appends = [
		'ready',
		'title',
	];

	public function Customer()
	{
		return $this->belongsTo(User::class);
	}

	public function Companies()
	{
		return $this->belongsToMany(User::class, 'company_report_company', NULL, 'company_id');
	}

	public function scopeCustomerId($query, $customerId)
	{
		return $query->where('customer_id', $customerId);
	}

	public function getReadyAttribute()
	{
		return $this->created_at != $this->updated_at ? true : false;
	}

	public function getTitleAttribute()
	{
		$v = new Verta($this->created_at);

		$date = str_replace('-', '/', $v->formatDate());

		return 'گزارش مالی تاریخ ' . $date;
	}
}
