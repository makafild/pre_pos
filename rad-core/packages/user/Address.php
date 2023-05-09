<?php

namespace Core\Packages\user;

use Core\Packages\gis\City;
use Core\Packages\gis\Areas;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Address
 *
 * @package App\Models\User
 *
 * @property string $address
 * @property string $postal_code
 * @property float  $lat
 * @property float  $long
 *
 * @property int    $user_id
 * @property Users   $user
 */
class Address extends Model
{
	protected $fillable = [
		'user_id',
		'address',
		'postal_code',
		'lat',
		'long',
	];

	protected $casts = [
		'lat' => 'float',
		'long' => 'float',
	];

	public function User()
	{
		return $this->belongsTo(Users::class);
	}
    public function city()
	{
		return $this->belongsTo(City::class);
	}
    public function area()
	{
		return $this->belongsTo(Areas::class);
	}
}
