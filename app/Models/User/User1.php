<?php

namespace App\Models\User;

use App\Models\Common\File;
use App\Models\Product\Brand;
use App\Models\Setting\City;
use App\Models\Setting\Constant;
use App\Models\Setting\Country;
use App\Models\Setting\Province;
use App\Notifications\MailResetPasswordToken;
use App\Traits\VersionObserve;
use Carbon\Carbon;
use Core\Packages\gis\Areas;
use Core\Packages\gis\UserArea;
use Core\Packages\gis\UserRoute;
use Hekmatinasser\Verta\Verta;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * Class User
 *
 * @package App\Models\User
 * @property int $id
 * @property string $email
 * @property string $password
 * @property string $mobile_number
 *
 * @property string $name_fa
 * @property string $name_en
 * @property string $economic_code
 * @property string $api_url
 * @property string $gateway_token
 * @property string $lat
 * @property string $long
 *
 * @property string $first_name
 * @property string $last_name
 * @property string $national_id
 *
 * @property string $title
 **
 * @property string $kind
 * @property string $status
 *
 * @property int $creator_id
 * @property User $creator
 *
 * @property int $company_id
 * @property User $company
 *
 * @property int $photo_id
 * @property File $photo
 *
 * @property Constant $categories
 *
 * @property int $introducer_code_id
 * @property IntroducerCode $IntroducerCode
 *
 * @property Brand[] $brands
 *
 * @property int $score
 * @property Score[] $scores
 *
 * @property Address[] $addresses
 * @property Contact[] $contacts
 *
 * @property Country[] $countries
 * @property Province[] $provinces
 * @property City[] $cities
 *
 * @property Role[] $roles
 * @property CompanyCustomer[] $referrals
 *
 * @property OneSignalPlayer[] $SignalPlayerIds
 *
 * @property CompanyToken $CompanyToken
 *
 * @property Carbon $end_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 *
 * @property int $effective_id
 * @property int $referral_id
 *
 * @method static User Admin()
 * @method static User Company()
 * @method static User MyCompany()
 * @method static User Customer()
 *
 * @method static User whereCities($city)
 * @method static User Active()
 *
 * @method User SearchName($title)
 */
class User extends Authenticatable
{
    use VersionObserve;
    use HasApiTokens, Notifiable, HasRoles, SoftDeletes;
    const KIND_COMPANY = 'company';

    const KIND_COMPANY_ADMIN = 'company_admin';
    const KIND_CUSTOMER = 'customer';
    const KIND_ADMIN = 'admin';

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    const STATUS = [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
    ];

    protected $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
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
        'customer_grade' ,
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

    protected $appends = [
        'title',
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
        return $this->hasOne(Contact::class)->where('kind', '=', 'phone');
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

    public function Areas()
    {
        return $this->hasMany(UserArea::class);
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

    // ********************************* Methods *********************************

    public function activate()
    {
        $this->status = self::STATUS_ACTIVE;
        $this->save();
    }

    public function deactivate()
    {
        $this->status = self::STATUS_INACTIVE;
        $this->save();
    }

    public function isActive()
    {
        return $this->status == self::STATUS_ACTIVE;
    }

    public function isInactive()
    {
        return $this->status == self::STATUS_INACTIVE;
    }

    public function isAdmin()
    {
        return $this->kind == self::KIND_ADMIN;
    }

    public function isCompany()
    {
        return $this->kind == self::KIND_COMPANY;
    }

    public function isCompanyAdmin()
    {
        return $this->kind == self::KIND_COMPANY_ADMIN;
    }

    public function isCustomer()
    {
        return $this->kind == self::KIND_CUSTOMER;
    }

    // ********************************* Scope *********************************

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeAdmin($query)
    {
        return $query->where('kind', self::KIND_ADMIN);
    }

    public function scopeCompany($query)
    {
        return $query->where('kind', self::KIND_COMPANY);
    }

    public function scopeMyCompany($query, $user = NULL)
    {
        if (!$user)
            $user = auth('mobile')->user();

        $cities = $user->Cities->pluck('id')->all();
        return $query->Company()
            ->Active()
            ->whereDate('end_at', '>', Carbon::now())
            ->WhereCities($cities);
    }

    public function scopeCompanyAdmin($query)
    {
        return $query->where('kind', self::KIND_COMPANY_ADMIN);
    }

    public function scopeCustomer($query)
    {
        return $query->where('kind', self::KIND_CUSTOMER);
    }

    public function scopeCompanyIdl($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeWhereCities($query, $cities)
    {
        return $query->whereHas('Cities', function ($query) use ($cities) {
            $query->whereIn('id', $cities);
        });
    }

    public function scopeSearchName($query, $title)
    {
        $query->where(function ($query) use ($title) {
            $query->where('name_en', 'like', "%{$title}%")
                ->orWhere('name_fa', 'like', "%{$title}%");
        });

        return $query;
    }

    // ********************************* Attributes *********************************

    public function getEffectiveIdAttribute()
    {
        $userId = NULL;
        if (auth('mobile')->user()->isAdmin()) {
            $userId = 1;
        } else if (auth('mobile')->user()->isCompany() || auth('mobile')->user()->isCompanyAdmin()) {
            $userId = auth('mobile')->user()->company_id;
        } else {
            $userId = $this->id;
        }

        return $userId;
    }

    public function getReferralIdAttribute()
    {
        if (auth('mobile')->user()->isCompany()) {
            foreach ($this->referrals as $referral) {
                if ($referral->company_id == auth('mobile')->user()->effective_id) {
                    return $referral->referral_id;
                }
            }
        }

        return NULL;
    }

    public function getTitleAttribute()
    {
        $title = '';
        if ($this->kind == self::KIND_COMPANY) {
            $title = $this->attributes['name_fa'];
        } else {
//			$title = $this->attributes['first_name'] ?? '' . ' ' . $this->attributes['last_name'] ?? '';
        }

        return $title;
    }

    public function getEndAtAttribute()
    {
        $v = new Verta($this->attributes['end_at']);

        return str_replace('-', '/', $v->formatDate());
    }

    public function getCreatedAtTranslateAttribute()
    {
        $v = new Verta($this->attributes['end_at']);

        return str_replace('-', '/', $v->formatDate());
    }

    public function getStatusTranslateAttribute()
    {
        return trans('translate.user.user.' . $this->status);
    }

    // ------------------------------ Methods --------------------------------

    public function routeNotificationForOneSignal()
    {
        $playerIds = $this->SignalPlayerIds->pluck('player_id')->unique()->toArray();
        $playerIds = array_unique($playerIds);
        $playerIds = array_values($playerIds);

        return $playerIds;
    }

    public function routeNotificationForFcm()
    {
        $playerIds = $this->SignalPlayerIds->where('provider', 'fcm')->pluck('player_id')->unique()->toArray();
        $playerIds = array_unique($playerIds);
        $playerIds = array_values($playerIds);

        return $playerIds;
    }


    public function getReferralIdBy($companyId)
    {
        foreach ($this->referrals as $referral) {
            if ($referral->company_id == $companyId) {
                return $referral->referral_id;
            }
        }

        return NULL;
    }

    /**
     * Send a password reset email to the user
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new MailResetPasswordToken($token));
    }

    public function setScoreAttribute($score)
    {
        $score = round($score, 1);

        $score = str_replace('.0', '', $score);

        $this->attributes['score'] = $score;
    }

}
