<?php

namespace App\Models\Common;

use App\Models\Product\Product;
use App\Models\Setting\City;
use App\Models\Setting\Constant;
use App\Models\Setting\Country;
use App\Models\Setting\Province;
use App\Models\User\User;
use App\Traits\VersionObserve;
use Carbon\Carbon;
use Hekmatinasser\Verta\Verta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Slider
 *
 * @package App\Models\Common
 * @property int        $id
 *
 * @property string     $kind
 *
 * @property int        $file_id
 * @property File       $file
 *
 * @property int        $company_id
 * @property User       $company
 *
 * @property int        $product_id
 * @property Product    $product
 *
 * @property string     $link
 *
 * @property string     $status
 *
 * @property Country[]  $countries
 * @property Province[] $provinces
 * @property City[]     $cities
 * @property Constant[] $categories
 *
 * @property Carbon     $start_at
 * @property Carbon     $end_at
 * @property Carbon     $created_at
 * @property Carbon     $updated_at
 *
 * @method static Slider Active()
 */
class Slider extends Model
{
	use VersionObserve, SoftDeletes;

	const STATUS_ACTIVE   = 'active';
	const STATUS_INACTIVE = 'inactive';

	const STATUS = [
		self::STATUS_ACTIVE,
		self::STATUS_INACTIVE,
	];

	const KIND_LINK    = 'link';
	const KIND_COMPANY = 'company';
	const KIND_PRODUCT = 'product';

	const KINDS = [
		self::KIND_LINK,
		self::KIND_COMPANY,
		self::KIND_PRODUCT,
	];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['deleted_at'];

	public function Company()
	{
		return $this->belongsTo(User::class)->withTrashed();
	}

	public function Product()
	{
		return $this->belongsTo(Product::class)->withTrashed();
	}

	public function File()
	{
		return $this->belongsTo(File::class, 'file_id');
	}


	public function Countries()
	{
		return $this->belongsToMany(Country::class, 'slider_country');
	}

	public function Provinces()
	{
		return $this->belongsToMany(Province::class, 'slider_province');
	}

	public function Cities()
	{
		return $this->belongsToMany(City::class, 'slider_city');
	}

	public function Categories()
	{
		return $this->belongsToMany(Constant::class, 'slider_category');
	}


	// ********************************* Attributes *********************************

	public function getStartAtAttribute()
	{
		$v = new Verta($this->attributes['start_at']);

		return str_replace('-', '/', $v->formatDate());
	}

	public function getEndAtAttribute()
	{
		$v = new Verta($this->attributes['end_at']);

		return str_replace('-', '/', $v->formatDate());
	}

	// ********************************* Scope *********************************

	public function scopeActive($query)
	{
		return $query->whereDate('start_at', '<', Carbon::now())
			->whereDate('end_at', '>', Carbon::now())
			->where('status', self::STATUS_ACTIVE);
	}
}
