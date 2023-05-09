<?php

namespace Core\Packages\shop;


use Core\Packages\common\File;
use EloquentFilter\Filterable;
use Core\Packages\shop\shop\ShopUser;
use Core\Packages\product\Product;
use Illuminate\Database\Eloquent\Model;
use Core\Packages\promotion\RewardBrand;
use Core\System\Exceptions\CoreException;
use Core\System\Http\Traits\SecureDelete;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * Class Brand
 *
 * @package App\Models\User
 * @property int    $id
 * @property string $name_en
 * @property string $name_fa
 *
 * @property int    $photo_id
 * @property File   $photo
 *
 */

class GeoArea extends Model
{
protected $table = 'geo_areas';

public function geo_area()
	{
		return $this->hasMany(Address::class);
	}

}
