<?php

namespace Core\Packages\shop;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CompanyCustomer
 *
 * @package App\Models\User
 * @property int $id
 * @property string $referral_id
 * @property string $email
 * @property string $mobile_number
 *
 * @property string $first_name
 * @property string $last_name
 * @property string $store_name
 * @property string $phone_number
 * @property string $payment_method_id
 * @property string $national_id
 *
 * @property int $price_class_id
 * @property string $price_class_price
 * @property array $address
 *
 * @property int $company_id
 * @property User $company
 *
 * @property int $customer_id
 * @property User $customer
 *
 * @method static CompanyCustomer CustomerId(integer $customer_id)
 * @method static CompanyCustomer CompanyId(integer $company_id)
 * @method static CompanyCustomer ReferralId(integer | array $referral_id)
 */
class CompanyCustomer extends Model
{
    protected $fillable = [
        "referral_id",
        "first_name",
        "last_name",
        "national_id",
        "economic_code",
        "email",
        "phone_number",
        "payment_method_id",
        "store_name",
        "mobile_number",
        "address",
        "price_class_id",
        "company_id",
        "customer_id"
    ];

    protected $appends = [
        'title',
    ];

    protected $casts = [
        'address' => 'array',
    ];

    public function scopeCompanyId($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeCustomerId($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeReferralId($query, $referralId)
    {
        if (!is_array($referralId))
            $referralId = [$referralId];

        return $query->where('referral_id', $referralId);
    }

    public function getTitleAttribute()
    {
        $values = [
            $this->referral_id,
            $this->first_name,
            $this->last_name,
            $this->mobile_number,
            $this->email,
        ];

        $values = array_values($values);
        return implode(' - ', $values);
    }
}
