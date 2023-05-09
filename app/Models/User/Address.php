<?php

namespace App\Models\User;

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
 * @property User   $user
 */
class Address extends Model
{
	protected $fillable = [
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
		return $this->belongsTo(User::class);
	}
}
