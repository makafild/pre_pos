<?php

namespace App\Models\Setting;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Country
 *
 * @package App\Models\Setting
 * @property int        $id
 * @property string     $name
 * @property Province[] $provinces
 */
class Country extends Model
{
	public function Countries()
	{
		return $this->hasMany(City::class);
	}

	public function Users()
	{
		return $this->belongsToMany(User::class, 'user_country');
	}
}
