<?php

namespace App\Models\Product;

use App\Models\Common\File;
use App\Models\User\User;
use App\Traits\VersionObserve;
use Hekmatinasser\Verta\Verta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Core\Packages\promotion\PromotionBrand;

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
 * @property User[] $companies
 */
class Brand extends Model
{
	use VersionObserve, SoftDeletes;

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['deleted_at'];

	protected $appends = [
		'created_at_translate',
	];

	public function Photo()
	{
		return $this->belongsTo(File::class);
	}

	public function Products()
	{
		return $this->hasMany(Product::class);
	}

	public function Companies()
	{
		return $this->belongsToMany(User::class, 'user_brand');
	}

    public function PromotionBrands()
    {
        return $this->hasMany(PromotionBrand::class);
    }

    public function getCreatedAtTranslateAttribute()
    {

//		$v = new Verta($this->created_at);
//
//		return $v->formatDate();
    }
}
