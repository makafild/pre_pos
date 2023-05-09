<?php

namespace Core\Packages\category;


use Core\Packages\common\File;
use Core\Packages\product\Product;
use Core\Packages\promotion\PromotionCategory;
use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;
use App\Models\Product\Promotions;

/**
 * Class Category
 *
 * @package App\Models\Product
 * @property string     $id
 * @property string     $title
 * @property int        $parent_id
 * @property Product[]  $products
 *
 * @property Category[] $children
 */
class Category extends Model
{
//	use VersionObserve;
	use NodeTrait;

	protected $appends = ['key', 'label', 'expanded'];

	public function Products()
	{
		return $this->hasMany(Product::class);
	}

	public function Photo()
	{
		return $this->belongsTo(File::class, 'photo_id');
	}

    public function PromotionCategories()
    {
//        return $this->belongsToMany(Promotions::class, 'promotions_category')
//            ->withPivot(['master', 'slave', 'slave2', 'total','category'])->where('status', Promotions::STATUS_ACTIVE);

        return $this->hasMany(PromotionCategory::class);
    }
	public function getKeyAttribute()
	{
		return $this->id;
	}

	public function getLabelAttribute()
	{
		return $this->title;
	}

	public function getExpandedAttribute()
	{
		return true;
	}
}
