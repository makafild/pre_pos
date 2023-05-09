<?php


namespace core\Packages\shop\src\controllers;

use Defuse\Crypto\Core;
use Illuminate\Http\Request;
use Core\Packages\shop\Users;
use App\Models\Setting\Monitor;
use App\Models\Setting\Setting;
use App\Rules\Jalali;
use Carbon\Carbon;
use Core\Packages\user\Address;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Core\System\Exceptions\CoreException;
// use Core\System\Exceptions\CoreExceptionOk;
use Core\Packages\tour_delivery\TourDelivery;
use Core\Packages\user\src\request\UserRequest;
use Core\Packages\user\src\request\LoginRequest;
use Core\System\Http\Controllers\CoreController;
use Core\Packages\user\src\request\UpdateUserRequest;
use core\Packages\setting\src\controllers\SettingController;
use Core\Packages\tour_delivery\DeliveryDates;
use Morilog\Jalali\Jalalian;

/**
 * Class UserPackageController
 *
 * @package Core\Packages\user\src\controllers
 */
class UserShopPackageController extends CoreController
{

    private $_register = [
        'email',
        'password',
        'first_name',
        'last_name',
        'mobile_number',
        'role_id',
        'photo_id',
        'kind',
    ];

    private $_login = [
        'email',
        'password'
    ];

    public function index()
    {

        /* $result = Users::_()->index();
        return $this->responseHandler2($result);*/
        if (auth('api')->user()['kind'] == 'superAdmin' || auth('api')->user()['kind'] == 'admin') {
            return Users::with('Group')->whereIn('kind', ['admin', 'superAdmin'])->orderBy('id', 'DESC')->jsonPaginate(10);
        }

        if (auth('api')->user()['kind'] == 'company') {
            return Users::with('Group')->where('kind', 'company')->where('company_id', auth('api')->user()->company_id)->orderBy('id', 'DESC')->jsonPaginate(10);
        }
    }

    /*  public function store(UserRequest $request)
    {
        $payload = $request->only($this->_register);
        $result = Users::_()->store($payload);
        return $this->responseHandler2($result);
    }*/

    /*  public function update(UserRequest $request, $id)
    {addressCities
        $payload = $request->only($this->_register);
        $result = Users::_()->updateUser($payload, $id);
        return $this->responseHandler2($result);
    }*/

    public function login(LoginRequest $request)
    {

        $payload = $request->only($this->_login);

        $result = Users::_()->login($payload);

        Monitor::addMonitor($result->result['user']->id, $result->result['user']->first_name . $result->result['user']->last_name . $result->result['user']->name_fa . $result->result['user']->name_en . "لاگین شد", "login");

        return $this->responseHandler2($result);
    }
    public function user_address()
    {
        $address = Address::where('user_id', auth('api')->user()->id)->with('city.Province' ,'city.Areas' )->orderBy('id', 'DESC')->paginate(5);
        return response($address);
    }
    public function address_show($id)
    {



        $address = Address::with('city.Province', 'area' )->where('user_id' , auth('api')->user()->id)->find($id);
        return response($address);

        // $address = Address::with([

        //     'user.Provinces' => function ($q) {
        //         return $q->select('id', 'name');
        //     },
        //     'user.Cities' => function ($q) {
        //         $q->select('id', 'name', 'province_id');
        //     },
        //     'user.Areas',



        // ])->where('user_id', auth('api')->user()->id)->find($id);
        // if ($address == null) {
        //     throw new CoreException('شناسه ادرس اشتباه است');
        // }
        // if ($address->user->areas != null) {
        //     $area = ['id' => $address->user->areas[0]->id, 'area' => $address->user->areas[0]->area];
        // } else {
        //     $area = [];
        // }



        // return response([
        //     'id' => $address->id,
        //     'address' => $address->address,
        //     'postal_code' => $address->postal_code,
        //     'provinces' => [
        //         'id' => $address->user->provinces[0]->id,
        //         'name' => $address->user->provinces[0]->name,
        //     ],
        //     'cities' => [
        //         'id' => $address->user->cities[0]->id,
        //         'name' => $address->user->cities[0]->name
        //     ],
        //     'area' => $area,
        // ]);
    }
    public function address_store(Request $request)
    {

        $address = new Address();
        $address->address = $request->address;
        $address->postal_code = $request->postal_code;
        $address->lat = $request->lat;
        $address->user_id = auth('api')->user()->id;
        $address->city_id = $request->cities ;
        $address->area_id = $request->areas ;
        $address->save();
        // $addon = Users::with('Provinces', 'Cities', 'Areas',)->find(auth('api')->user()->id);

        // $addon->Provinces()->sync($request->provience);
        // $addon->Cities()->sync($request->cities);
        // $addon->Areas()->sync($request->areas);


        // throw new CoreExceptionOk();
        return response(
            [ 'status' => true,
            'message' => 'ادرس با موفقیت ثبت شد',
            ]
        );
    }


    public function address_update(Request $request, $id)
    {

        $address = Address::where('user_id', auth('api')->user()->id)->find($id);
        $address->address = $request->address;
        $address->postal_code = $request->postal_code;
        $address->user_id = auth('api')->user()->id;
        $address->city_id = $request->cities ;
        $address->area_id = $request->areas ;
        $address->save();
        // $addon = Users::with('Provinces', 'Cities', 'Areas',)->find(auth('api')->user()->id);
        // $addon->Provinces()->sync($request->provience);
        // $addon->Cities()->sync($request->cities);
        // $addon->Areas()->sync($request->areas);
        // throw new CoreExceptionOk();
        return response(
            [ 'status' => true,
            'message' => "ادرس با موفقیت بروزرسانی شد",
            ]
        );

    }
    public function address_delete($id)
    {
        $address = Address::findOrFail($id)->delete();

        // throw new CoreExceptionOk();
        return response(
            [ 'status' => true,
            'message' => 'با موفقیت حذف شد',
            ]
        );


    }



    public function logout()
    {

        Monitor::addMonitor(auth('api')->user()['id'], auth('api')->user()['first_name'] . auth('api')->user()['last_name'] . auth('api')->user()['name_fa'] . auth('api')->user()['name_en'] . "خروج کرد", "logout");

        auth('api')->logout();


    }
    public function refreshToken()
    {
        $token = auth('api')->refresh();
        return $this->responseHandler2((object)['result' => ['access_token' => $token]]);
    }

    public function profile()
    {
        $result = Users::_()->profile();
        return $this->responseHandler2($result);
    }

    public function states(Request $request)
    {
        //return $this->responseHandler2((object)['result' => Users::_()::STATUS]);
        foreach ($request->id as $id) {
            if (auth('api')->user()['kind'] == 'superAdmin' || auth('api')->user()['kind'] == 'admin')
                $user = Users::find($id);
            else
                $user = Users::where('company_id', $id)->first();

            if (!empty($user)) {
                $user->status = $request->value;
                $user->save();
            }
        }
        return [
            'status' => true,
            'message' => trans('با موفقیت ویرایش شد'),
        ];
    }


    public function loginAs($id)
    {
        if (!(auth('api')->user()['kind'] == 'superAdmin' || auth('api')->user()['kind'] == 'admin')) {
            throw new CoreException('دسترسی غیر مجاز');
        }
        $user = Auth::loginUsingId($id);
        if (empty($user)) {
            return $this->responseHandler2((object)['message' => 'شناسه کاربر نامعتبر است']);
        }
        $token = JWTAuth::fromUser($user);
        if (is_null($user)) {
            return $this->responseHandler2((object)['message' => 'خطا در دریافت توکن']);
        }
        //        $currentUser = Auth::user();
        //        $exp = JWTAuth::setToken($token)->getPayload()->get('exp');
        $result = Users::_()->createNewToken($token, $user);
        return $this->responseHandler2($result);
    }



    public function store(UserRequest $request)
    {

        $kind = "";
        $company_id = "";
        if (!(auth('api')->user()['kind'] == 'superAdmin' || auth('api')->user()['kind'] == 'admin')) {
            $kind = "company";
            $company_id = auth('api')->user()->company_id;
        } else {
            $kind = "admin";
            $company_id = "";
        }
        if (auth('api')->user()['kind'] == 'consumer') {
            $kind = "consumer";
        }
        $customer = new Users();
        $customer->email = $request->email;
        $customer->mobile_number = $request->mobile_number;
        if ($request->password)
            $customer->password = bcrypt($request->password);
        $customer->first_name = $request->first_name;
        $customer->kind = $kind;
        $customer->last_name = $request->last_name;

        if ($company_id)
            $customer->company_id =  $company_id;

        $customer->save();

        return [
            'status' => true,
            'message' => 'کاربر با موفقیت ثبت شد'
        ];
    }

    public function update(UpdateUserRequest $request, $id)
    {
        $customer = Users::where('company_id', auth('api')->user()->company_id)->where('id', $id)->first();
        if (!$customer)
            throw new CoreException('مورد یافت نشد');
        $customer->email = $request->email;
        $customer->mobile_number = $request->mobile_number;
        if ($request->password)
        $customer->password = bcrypt($request->password);
        $customer->first_name = $request->first_name;
        $customer->last_name = $request->last_name;
        $customer->photo_id = $request->photo_id;
        $customer->group_id = $request->group_id;

        $customer->save();

        return [
            'status' => true,
            'message' => trans('کاربر با موفقیت ویرایش شد'),
        ];
    }

    public function destory(Request $request, Users $user)
    {
        if (!$request->ids)
            throw new CoreException('لیست ایدی ها الزامی می باشد');

        $user->secureDelete($request->ids, ['CompanyRel', 'Areas', 'Products', 'Orders', 'Addresses']);
        return [
            'status' => true,
            'message' => "کاربر با موفقیت حذف شد",
        ];
    }

    public function show($id)
    {
        return  Users::with(['Group', 'Photo'])->where('id', auth('api')->user()->id)->first();
    }

    public function changeStates(Request $request)
    {

        foreach ($request->id as $id) {
            $user = Users::where('company_id', auth('api')->user()->company_id)->where('id', $id)->first();
            if (!empty($user)) {
                $user->status = $request->value;
                $user->save();
            }
        }
        return [
            'status' => true,
            'message' => trans('messages.customer.customer.changeStatus'),
        ];
    }




    public function dates(Request $request){
        $date = date('Y-m-d'); //today date
        $weekOfdays = array();
        for($i =1; $i <= 7; $i++){
          $date = date('Y-m-d', strtotime('+1 day', strtotime($date)));
          $weekOfdays[] = date('Y-m-d', strtotime($date));
        }

        foreach($weekOfdays as $index=>$weekdays){
            dd(empty(DeliveryDates::where('date_times', $weekdays)->get()));
            if(!empty(DeliveryDates::where('date_times', $weekdays))){
                $weekOfdays[$index] =  $weekOfdays[$index] . ' false' ;
            }
        }
        dd($weekOfdays);
        // $Members = DeliveryDates::whereIn('date_times', $weekOfdays)->get();



        //  return    $result = TourDelivery::with('dates.times')->whereIn('company_id' ,$request->ids )->get()->chunk(10);


    }
}
