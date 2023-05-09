<?php

namespace Core\Packages\shop;



use Core\Packages\gis\City;

use EloquentFilter\Filterable;

use Core\Packages\user\Address;
use Core\Packages\common\Constant;
use Core\Packages\shop\File;
use Illuminate\Database\Eloquent\Model;
use Core\Packages\shop\Contact as ShopContact;

class User extends Model
{
    protected $table = 'users';
    protected $fillable = [
        'id',
        'email',
        'password',
        'mobile_number',
        'phone_number',
        'store_name',
        'first_name',
        'last_name',
        'photo_id',
        'kind',
        'crm_api',
        'company_id',
        'description',
        'api_service',
        'referral_id',
        'crm_registered',
        'introduction_source',
        'customer_grade',
        'customer_group',
        'customer_class',
        'national_id',
        'introducer_code_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $dates = [
        'effective_id',
        'end_at',
        'deleted_at',
    ];


    protected $casts = [
        'score' => 'double',
    ];

    // ********************************* Relations *********************************

    public function Creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function Photo()
    {
        return $this->belongsTo(File::class);
    }

    public function Brands()
    {
        return $this->belongsToMany(Brand::class, 'user_brand');
    }
    public function UsePromotion()
    {
        return $this->hasMany(UserPromotion::class);
    }
    public function Categories()
    {
        return $this->belongsToMany(Constant::class, 'user_category');
    }

    public function Scores()
    {
        return $this->hasMany(Score::class);
    }

    public function Addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function Contacts()
    {
        return $this->hasMany(Contact::class);
    }
    public function ContactsPhone()
    {
        return $this->hasOne(ShopContact::class)->where('kind', '=', 'phone');
    }

    public function Countries()
    {
        return $this->belongsToMany(Country::class, 'user_country');
    }

    public function Provinces()
    {
        return $this->belongsToMany(Province::class, 'user_province');
    }

    public function Cities()
    {
        return $this->belongsToMany(City::class, 'user_city');
    }
    public function Route()
    {
        return $this->belongsToMany(Routes::class, 'user_route','user_id','route_id');
    }

    public function Areas()
    {
        return $this->hasMany(UserArea::class);
    }
    public function Area()
    {
        return $this->belongsToMany(Area::class, 'user_area');
    }

    public function Routes()
    {
        return $this->hasMany(UserRoute::class);
    }


    public function Referrals()
    {
        return $this->hasMany(CompanyCustomer::class, 'customer_id');
    }

    public function PriceClasses()
    {
        return $this->belongsToMany(PriceClass::class, 'price_class_customer', 'customer_id');
    }

    public function SignalPlayerIds()
    {
        return $this->hasMany(OneSignalPlayer::class);
    }

    public function CompanyUser()
    {
        return $this->belongsTo(User::class, 'company_id');
    }


    public function CompanyToken()
    {
        return $this->hasOne(CompanyToken::class, 'company_id');
    }

    public function IntroducerCode()
    {
        return $this->belongsTo(IntroducerCode::class);
    }



}
