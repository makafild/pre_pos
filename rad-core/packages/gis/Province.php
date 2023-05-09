<?php

namespace Core\Packages\gis;
use Core\Packages\user\Users;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Constraint\Count;

/**
 * Class Province
 *
 * @package App\Models\Setting
 * @property int $id
 * @property string $name
 * @property City[] $cities
 * @property Country $country
 * @property int $country_id
 * @property arprovincesray $CitiesList
 */
class Province extends Model
{
	protected $fillable = [
		'name',
	];
    protected $hidden = ['created_at', 'updated_at'];
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
		return $this->belongsToMany(Users::class, 'user_province');
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
