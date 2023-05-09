<?php

namespace App\Models\Product;

use App\Models\Common\File;
use App\Traits\VersionObserve;
use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;

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
	use VersionObserve;
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
    public function Promotions()
    {
        return $this->belongsToMany(Promotions::class, 'promotions_category')
            ->withPivot(['master', 'slave', 'slave2', 'total'])->where('status', Promotions::STATUS_ACTIVE);
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
