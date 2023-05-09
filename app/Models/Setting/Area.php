<?php

namespace App\Models\Setting;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Class City
 *
 * @package App\Models\Setting
 * @property string $id
 * @property string $name
 * @property Province $province
 * @property int $province_id
 */
class Area extends Model
{
	protected $fillable = [
		'name',
	];




	public function Users()
	{
		return $this->belongsToMany(User::class, 'user_area');
	}

}
