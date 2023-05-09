<?php


namespace Core\Packages\visitor;


use DB;
use App\BaseModel;
use Carbon\Carbon;
use Core\Packages\gis\Routes;
use Core\Packages\user\Users;
use Core\Packages\group\Group;
use EloquentFilter\Filterable;

use App\ModelFilters\VisitorFilter;
use Illuminate\Support\Facades\Hash;
use Core\System\Http\Traits\HelperTrait;
use Core\System\Exceptions\CoreException;

class Visitors extends BaseModel
{
    use HelperTrait;
    use Filterable;
    public $timestamps = true;

    protected $fillable = [
        "ref_id"
    ];

    private static $_instance = null;
    public static function _()
    {
        if (self::$_instance == null) {
            self::$_instance = new Visitors();
        }
        return self::$_instance;
    }
    public function Routes()
    {
        return $this->belongsToMany(Routes::class, 'visitor_route', "visitor_id", 'route_id');
    }
    public function list($request, $id = '', $type = null)
    {

        $company_id = 0;

        $query = $this->orderBy('id', 'desc');

        if (!empty($id)) {
            $result = $this->find($id);
            if (!isset($result)) {
                throw new CoreException(' شناسه ' . $id . ' یافت نشد');
            }
            $result = $query->with('user.Group', 'visitors', 'superVisitor')->where('id', $id);
            if (auth('api')->user()->kind == 'company')
                $result->whereHas('user', function ($q) {
                    $q->where('company_id', auth('api')->user()->company_id);
                });

            $result = $result->first();
        } else {
            if (auth('api')->user()->kind == 'company')
                $company_id = auth('api')->user()->company_id;


            $result = $query->whereHas('user', function ($query) use ($id, $company_id) {
                if ($company_id)
                    $query->where('company_id', $company_id);
            })->with('user.Group', 'visitors', 'superVisitor')->filter($request->all(), VisitorFilter::class)->get();
        }
        return $this->modelResponse(['data' => $result, 'count' => !empty($id) ? 1 : count($result)]);
    }


    public function store($payload)
    {



        if (!empty($payload['email'])) {
            $searchUser = Users::where('email', $payload['email'])->first();
            if ($searchUser) {
                throw new CoreException(' شناسه ایمیل ' . $payload['email'] . 'تکراری است');
            }
        }
        $user_data = [
            "password" => Hash::make($payload['password']),
            "mobile_number" => $payload['mobile_number'],
            "first_name" => $payload['first_name'],
            "last_name" => $payload['last_name'],
            "group_id" => $payload['group_id'],
            "company_id" => auth('api')->user()->company_id
        ];

        if (!empty($payload['email'])) {
            $user_data['email'] = $payload['email'];
        }

        $userId = Users::insertGetId($user_data);
        $data = [
            'user_id' => $userId,
            "is_super_visitor" => $payload['is_super_visitor'],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];
        $parent = $this->insertGetId($data);
        if ($payload['is_super_visitor'] == true) {
            if ($payload['visitors'] and count($payload['visitors']) > 0) {
                foreach ($payload['visitors']  as $visitor) {
                    $this->find($visitor)->update([
                        'ref_id' => $parent
                    ]);
                }
            }
        }
        return $userId;
    }
    public function updateR($id, $payload)
    {
        $Visitor = Visitors::with('visitors')->find($id);

        if ($Visitor) {
            $userVisitorId = $Visitor->user->id;

            $User = Users::find($userVisitorId);

            if ($User) {

                if ($this->ISCompany() && $User->company_id != auth('api')->user()->company_id) {
                    throw new CoreException('مورد یافت نشد');
                }
                $user_data = [
                    "mobile_number" => (string)$payload['mobile_number'],
                    "first_name" => $payload['first_name'],
                    "last_name" => $payload['last_name'],
                    "group_id" => $payload['group_id']
                    //,  "company_id" => auth('api')->user()->company_id
                ];
                if (!empty($payload['email'])) {
                    $user_data['email'] = $payload['email'];
                }

                if (isset($payload['password'])) {
                    $user_data['password'] = Hash::make($payload['password']);
                }
                $userId = Users::find($userVisitorId)->update($user_data);
                $data = [
                    'user_id' => $userId,
                    "is_super_visitor" => $payload['is_super_visitor'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
                $parent = $Visitor->update($data);
                if ($payload['is_super_visitor'] == true) {
                    $Visitors = $Visitor->visitors->pluck('id')->toArray();
                    if ($Visitors) {
                        $this->whereIn('id', $Visitors)->update([
                            'ref_id' => null
                        ]);
                    }
                    if ($payload['visitors'] and count($payload['visitors']) > 0) {

                        foreach ($payload['visitors']  as $visitor) {
                            $this->where('id', $visitor)->update([
                                'ref_id' => $Visitor->id
                            ]);
                        }
                    } else {
                    }
                }
            }
        }
    }
    public function destroyRecord($id)
    {
        $record = $this->whereIn('id', $id)->get();
        if ($record) {
            $users =  Users::whereIn('id', collect($record)->pluck('user_id')->all())->get();
            $record->each->delete();
            $users->each->delete();
        }
    }

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id');
    }
    public function superVisitor()
    {
        return $this->hasOne(Visitors::class, 'id', 'ref_id')->with('user');
    }
    public function visitors()
    {
        return $this->hasMany(Visitors::class, 'ref_id', 'id')->with('user');
    }
    
}
