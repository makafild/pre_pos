<?php

namespace App\Models\Setting;

use App\Models\User\User;
use Core\Packages\gis\Areas;
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
class City extends Model
{
	protected $fillable = [
		'name',
	];


    public function Province()
	{
		return $this->belongsTo(Province::class);
	}

	public function Users()
	{
		return $this->belongsToMany(User::class, 'user_city');
	}
    public function Areas()
    {
        return $this->hasMany(Areas::class);
    }
}
