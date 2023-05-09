<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Contact
 *
 * @package App\Models\User
 * @property string $kind
 * @property string $value
 *
 * @property int    $user_id
 * @property User   $user
 */
class Contact extends Model
{
	public function User()
	{
		return $this->belongsTo(User::class);
	}
}
