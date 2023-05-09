<?php

namespace Core\Packages\common;


use Core\Packages\user\Users;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MessageList
 *
 * @package App\Models\Common
 *
 * @property int    $user_id
 * @property User   $to
 * @property string $message
 *
 * @property Carbon $seen_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @method static MessageList Mine()
 * @method static MessageList ToMe()
 * @method static MessageList From($user_id)
 */
class MessageList extends Model
{
	protected $fillable = [
		'user_id',
		'to_id',
		'message',
	];

	const UPDATED_AT = NULL;

	public function User()
	{
		return $this->belongsTo(Users::class);
	}

	public function To()
	{
		return $this->belongsTo(Users::class);
	}

	public function scopeMine($query)
	{
		return $query->where('user_id', auth('api')->user()->effective_id);
	}

	public function scopeToMe($query)
	{
		return $query->where('to_id', auth('api')->user()->effective_id);
	}

	public function scopeFrom($query, $id)
	{
		return $query->where('user_id', $id);
	}
}
