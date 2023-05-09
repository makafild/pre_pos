<?php

namespace core\Packages\user\src\controllers;

use App\Models\Setting\Monitor;
use Illuminate\Http\Request;
use Core\Packages\user\Users;
use App\Models\Setting\Setting;
use Tymon\JWTAuth\Facades\JWTAuth;

use Illuminate\Support\Facades\Auth;
use Core\System\Exceptions\CoreException;
use Core\Packages\user\src\request\UserRequest;
use Core\Packages\user\src\request\LoginRequest;
use Core\System\Http\Controllers\CoreController;
use Core\Packages\user\src\request\UpdateUserRequest;
use core\Packages\setting\src\controllers\SettingController;


/**
 * Class UserPackageController
 *
 * @package Core\Packages\user\src\controllers
 */
class UserPackageController extends CoreController
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
    {
        $payload = $request->only($this->_register);
        $result = Users::_()->updateUser($payload, $id);
        return $this->responseHandler2($result);
    }*/

    public function login(LoginRequest $request)
    {
        $payload = $request->only($this->_login);
        $result = Users::_()->login($payload);

       Monitor::addMonitor($result->result['user']->id,$result->result['user']->first_name.$result->result['user']->last_name.$result->result['user']->name_fa.$result->result['user']->name_en."لاگین شد","login");
        return $this->responseHandler2($result);
    }

    public function logout()
    {
        Monitor::addMonitor(auth('api')->user()['id'],auth('api')->user()['first_name'].auth('api')->user()['last_name'].auth('api')->user()['name_fa'].auth('api')->user()['name_en']."خروج کرد","logout");
        auth('api')->logout();
        return $this->responseHandler2([]);
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
            $user=Users::where('company_id',$id)->first();

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

        $customer = new Users();
        $customer->email = $request->email;
        $customer->mobile_number = $request->mobile_number;
        if ($request->password)
            $customer->password = bcrypt($request->password);

        $customer->first_name = $request->first_name;
        $customer->kind = $kind;
        $customer->last_name = $request->last_name;
        $customer->photo_id = $request->photo_id;
        $customer->group_id = $request->group_id;
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

    public function destory(Request $request , Users $user)
    {
        if (!$request->ids)
            throw new CoreException('لیست ایدی ها الزامی می باشد');

            $user->secureDelete($request->ids , ['CompanyRel','Areas','Products','Orders','Addresses']);
        return [
            'status' => true,
            'message' => "کاربر با موفقیت حذف شد",
        ];
    }

    public function show($id)
    {
        return  Users::with(['Group', 'Photo'])->where('company_id', auth('api')->user()->company_id)->where('id', $id)->first();
    }

    public function changeStates(Request $request)
    {
        dd("g");
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
}
