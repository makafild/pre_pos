<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

/**
 * Class OneSignalPlayer
 *
 * @package App\Models\User
 * @property string $player_id
 * @property string $provider
 *
 * @property int    $user_id
 * @property User   $user
 */
class OneSignalPlayer extends Model
{
	public function User()
	{
		return $this->belongsTo(User::class);
	}
}
