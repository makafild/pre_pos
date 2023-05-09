<?php

namespace Core\Packages\price_class;


use Core\Packages\product\Product;
use Core\Packages\user\Users;
use Core\System\Exceptions\CoreException;
use Core\System\Http\Traits\HelperTrait;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PriceClass
 *
 * @package App\Models\User
 * @property integer $id
 * @property integer $referral_id
 * @property string  $title
 *
 * @property int     $company_id
 * @property User    $Company
 *
 * @property User[]  $Customers
 *
 * @method static PriceClass ReferralId(int $referralId)
 */
class PriceClass extends Model
{
    use HelperTrait,Filterable;
    protected $fillable = [
        'title'
    ];
    private static $_instance = null;
    public static function _()
    {
        if (self::$_instance == null) {
            self::$_instance = new PriceClass();
        }
        return self::$_instance;
    }

	public function Company()
	{
		return $this->belongsTo(Users::class)->withTrashed();
	}

	public function Customers()
	{
		return $this->belongsToMany(Users::class, 'price_class_customer', 'price_class_id', 'customer_id');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function Products()
	{
		return $this->belongsToMany(Product::class, 'price_class_product', 'price_class_id', 'product_id')
			->withPivot(['price']);
	}

	//

	public function scopeCompanyId($query, $companyId)
	{
		if ($companyId)
			return $query->where('company_id', $companyId);

		return $query;
	}

	public function scopeReferralId($query, $referralId)
	{
		return $query->where('referral_id', $referralId);
	}
    public function validate($payload){

    }
    public function list($id='')
    {
        $query = $this->orderBy('id', 'desc');
        if (request('company_id'))
            $companyId = request('company_id');
        else
            $companyId = auth('api')->user()->company_id;

        if (!empty($id)) {
            $result = $this->find($id);
            if (!isset($result)) {
                throw new CoreException(' شناسه ' . $id . ' یافت نشد');
            }
            $result=$query->with('company')->where('price_classes.company_id', $companyId)->where('id',$id)->first();
        }else{
            $result=$query->get();
        }
        return $this->modelResponse(['data' => $result, 'count' => !empty($id)?1:count($result)]);
    }
    public function destroyRecord($id){

        return $this->destroyRow($id);
    }
}
