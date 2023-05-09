<?php

namespace App\Models\Setting;

use App\Models\Product\Brand;
use App\Models\User\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Constant
 *
 * @package App\Models\Setting
 * @property int    $id
 *
 * @property string $constant_fa
 * @property string $constant_en
 * @property string $kind
 *
 * @method static Constant Kind($kind)
 */
class Constant extends Model
{
	use SoftDeletes;

	const CUSTOMER_CATEGORY = 'customer_category';
	const CUSTOMER_CLASS = 'customer_class';
	const UNIT = 'unit';
	const ADDITIONS = 'additions';
	const PRODUCT_LABEL = 'product_label';
	const PAYMENT_METHOD = 'payment_method';
	const INVOICE_TITLE = 'invoice_title';
    const SUBLAYER = 'sublayer';
    const PROVIDERS = "provider";
    const CUSTOMER_GROUP = 'customer_group';
    const CUSTOMER_GRADE = 'customer_grade';
	const CONSTANT_KINDS = [
		self::CUSTOMER_CATEGORY,
		self::UNIT,
		self::ADDITIONS,
		self::SUBLAYER,
		self::CUSTOMER_CLASS,
		self::PROVIDERS,
		self::PRODUCT_LABEL,
		self::PAYMENT_METHOD,
		self::INVOICE_TITLE,
        self::CUSTOMER_GROUP,
        self::CUSTOMER_GRADE,
	];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['deleted_at'];

	protected $appends = [
		'kind_translate',
		'constant',
	];

	public function scopeKind($query, $kind)
	{
		return $query->where('kind', $kind);
	}

	public function getKindTranslateAttribute()
	{
		return trans("translate.setting.constant.$this->kind");
	}

	public function getConstantAttribute()
	{
		$name = 'constant_' . \App::getLocale();

		return !empty( $this->attributes[$name])?$this->attributes[$name]:'';
	}
}
