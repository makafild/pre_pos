<?php

namespace Core\Packages\shop;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Contact
 *
 * @package App\Models\User
 * @property string $kind
 * @property string $value
 *
 * @property int    $user_id
 * @property Users   $user
 */
class Contact extends Model
{
	public function User()
	{
		return $this->belongsTo(Users::class);
	}
}
