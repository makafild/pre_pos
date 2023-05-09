<?php

namespace App\Models\Setting;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Constraint\Count;

/**
 * Class Province
 *
 * @package App\Models\Setting
 * @property string $name
 * @property City[] $cities
 * @property Country $country
 * @property int $country_id
 * @property array $CitiesList
 */
class Province extends Model
{
	protected $fillable = [
		'name',
	];

	public function Country()
	{
		return $this->belongsTo(Country::class);
	}

	public function Cities()
	{
		return $this->hasMany(City::class);
	}

	public function Users()
	{
		return $this->belongsToMany(User::class, 'user_province');
	}

	public function getCitiesListAttribute()
	{
		$citiesList = [];

		foreach ($this->cities as $city) {
			$cityList['id'] = $city->id;
			$cityList['name'] = $city->name;

			$citiesList[] = $cityList;
		}

		return $citiesList;
	}
}
