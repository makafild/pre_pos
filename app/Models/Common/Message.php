<?php

namespace App\Models\Common;

use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * Class Message
 *
 * @package App\Models\Common
 *
 * @property int    $id
 *
 * @property int    $from_id
 * @property User   $From
 *
 * @property int    $to_id
 * @property User   $To
 *
 * @property string $message
 * @property Carbon $created_at
 */
class Message extends Model
{
	protected $fillable = [
		'from_id',
		'to_id',
		'message',
	];

	const UPDATED_AT = NULL;

	public function From()
	{
		return $this->belongsTo(User::class);
	}

	public function To()
	{
		return $this->belongsTo(User::class);
	}

	public function scopeMine($query)
	{
		$query->where('from_id', auth('mobile')->user()->effective_id)
			->orWhere('to_id', auth('mobile')->user()->effective_id);

		return $query;
	}

	public function scopeAudience($query, $id)
	{
		$query->where('from_id', $id)
			->orWhere('to_id', $id);

		return $query;
	}
}
