<?php

namespace App\Models\User;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class SmsValidation
 *
 * @package App\Models\User
 *
 * @property string $code
 * @property string $kind
 * @property string $mobile_number
 *
 * @property Carbon $created_at
 */
class SmsValidation extends Model
{
	use SoftDeletes;

	const KIND_CONFIRM = 'confirm';
	const KIND_FORGET = 'forget';

	protected $dates = [
		'deleted_at'
	];
}
