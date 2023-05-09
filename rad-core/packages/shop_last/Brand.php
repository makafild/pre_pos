<?php

namespace Core\Packages\shop;

use Core\Packages\shop\User as brandUser;
use Core\Packages\common\File;
use EloquentFilter\Filterable;
use Core\Packages\shop\shop\ShopUser;
use App\ModelFilters\BrandFilter;
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
class Brand extends Model
{
    protected $table = 'brands';
    use Filterable;
    use SecureDelete;
	use  SoftDeletes;
	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['deleted_at'];

	protected $appends = [
		'created_at_translate',
	];
    private static $_instance = null;

    public static function _()
    {
        if (self::$_instance == null) {
            self::$_instance = new Brand();
        }
        return self::$_instance;
    }

    public function modelFilter()
    {
        return $this->provideFilter(BrandFilter::class);
    }
	public function Photo()
	{
		return $this->belongsTo(File::class);
	}

	public function Products()
	{
		return $this->hasMany(Product::class);
	}
    public function rewards()
	{
		return $this->hasMany(RewardBrand::class);
	}

	public function Companies()
	{
        return $this->belongsToMany(brandUser::class, 'user_brand');
	}

	public function getCreatedAtTranslateAttribute()
	{
      return $this->created_at;


	}
    public function list($id='')
    {
        $query = $this->orderBy('id', 'desc');

        if (!empty($id)) {
            $result = $this->find($id);
            if (!isset($result)) {
                throw new CoreException(' شناسه ' . $id . ' یافت نشد');
            }
            $result=$query->with('photo', 'companies')->where('id',$id)->first();
        }else{
            $result=$query->get();
        }
        return $this->modelResponse(['data' => $result, 'count' => !empty($id)?1:count($result)]);
    }

}
