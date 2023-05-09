<?php

namespace Core\Packages\gis;


use Core\Packages\user\Users;
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
    protected $hidden = ['created_at', 'updated_at'];
	public function Countries()
	{
		return $this->hasMany(City::class);
	}
    public function provinces()
    {
        return $this->hasMany(Province::class);
    }
	public function Users()
	{
		return $this->belongsToMany(Users::class, 'user_country');
	}
}
