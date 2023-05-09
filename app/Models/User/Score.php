<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Score
 *
 * @package App\Models\User
 * @property int $score
 *
 * @property int $user_id
 * @property User $user
 *
 * @property int $recipient_id
 * @property User $recipient
 */
class Score extends Model
{
	protected $table = 'user_scores';

	protected $fillable = [
		'user_id',
		'recipient_id',
		'score',
	];

	public function User()
	{
		return $this->belongsTo(User::class);
	}

	public function Recipient()
	{
		return $this->belongsTo(User::class);
	}
}
