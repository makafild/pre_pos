<?php

namespace App\Http\Controllers\api\Customer\v1\User;

use App\SMS;
use Exception;
use App\Models\User\User;
use Illuminate\Http\Request;
use Core\System\Helper\CrmSabz;
use App\Models\User\IntroducerCode;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Core\System\Exceptions\CoreException;
use App\Http\Requests\api\Customer\v1\User\RegisterRequest;
use App\Http\Requests\api\Customer\v1\User\customRegisterRequest;
use App\Models\Setting\City;

class RegisterController extends Controller
{

    public function register(RegisterRequest $request)
    {
        try {
            CrmSabz::_()->checkCorectData($request);
            $checkMobilePhone = CrmSabz::_()->checkMobilePhone($request->mobile_number, !empty($request->phone_number) ? $request->phone_number : null);
            if ($checkMobilePhone == true) {
                return [
                    'status' => true,
                    'message' => trans('messages.api.company.customer.store'),
                ];
            }
        } catch (Exception $e) {

            return [
                'status' => true,
                'message' => trans('پاسخی از crm  دریافت نشد'),
            ];
        }



        $referralId = '';
        $storeCrm = CrmSabz::_()->storeCrm('create', 'app', $request, '');
        if ($storeCrm) {
            $crmRegistered = 1;
            $referralId = $storeCrm->id;
        } else {
            $crmRegistered = -1;
            throw new CoreException(' خطا در ثبت اطلاعات crm');
        }

        $user = $this->create($request->all());
        $user->status = User::STATUS_INACTIVE;
        $user->approve = 0;

        $user->crm_registered = $crmRegistered;
        if (!empty($referralId)) {
            $user->referral_id = $referralId;
        }

        $user->save();
        if ($request->introduction_id) {
            $introducerCode = IntroducerCode::where('code', $request->introduction_id)
                ->where('status', 'active')
                ->first();

            if ($introducerCode)
                $introducerCode->introducer_code_id = $request->introduction_id;
        }


        if ($request->categories)
            $user->categories()->attach($request->categories);

        if ($request->price_classes)
            $user->PriceClasses()->sync($request->price_classes ?? NULL);

        $user->addresses()->create($request->all());
        $user->countries()->sync($request->country);
        $user->Provinces()->sync($request->province);
        //this lines is error for register user,I commit it
        // $user->Areas()->sync($request->area);
        // $user->Routes()->sync($request->route);
        $user->Cities()->sync($request->city);

        auth()->login($user);

        (new UserController())->sendSmsCode($user->mobile_number);

        $token = $user->createToken('Customer v1')->accessToken;
        $message = "";
        if ($user->mobile_number && ($user->first_name || $user->last_name))
            $message = trans('messages.api.customer.user.register');
        else
            $message = "اطلاعات مشتری سمت crm  ناقص می باشد لطفا با پشتیبانی تماس بگیرید";

        return [
            'status' => true,
            'user_id' => $user->id,
            'message' =>    $message ,
            'access_token' => $token,
        ];
    }

    /**
     * @param RegisterRequest $request
     *
     * @return array
     */
    public function guest(Request $request)
    {
        /** @var User $user */
        $user = new User();
        $user->kind = User::KIND_CUSTOMER;
        $user->status = User::STATUS_ACTIVE;
        $user->first_name = 'Guest';
        $user->last_name = '';
        $user->save();

        $user->countries()->sync([2]);
        $user->Provinces()->sync([34]);
        $user->Cities()->sync([442]);

        auth()->login($user);

        $token = $user->createToken('Customer Guest')->accessToken;

        return [
            'status' => true,
            'user_id' => $user->id,
            'message' => trans('messages.api.customer.user.register'),
            'access_token' => $token,
        ];
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     *
     * @return User
     */
    protected function create(array $data)
    {
        $data['password'] = Hash::make($data['password']);

        return User::create($data);
    }



    public function custom(customRegisterRequest $request)
    {
        try {
            CrmSabz::_()->checkCorectData($request);
            $checkMobilePhone = CrmSabz::_()->checkMobilePhone($request->mobile_number, !empty($request->phone_number) ? $request->phone_number : null);
            if ($checkMobilePhone == true) {
                return [
                    'status' => true,
                    'message' => trans('messages.api.company.customer.store'),
                ];
            }
        } catch (Exception $e) {

            return [
                'status' => true,
                'message' => trans('پاسخی از crm  دریافت نشد'),
            ];
        }



        $referralId = '';
        $storeCrm = CrmSabz::_()->storeCrm('create', 'app', $request, '');
        if ($storeCrm) {
            $crmRegistered = 1;
            $referralId = $storeCrm->id;
        } else {
            $crmRegistered = -1;
            throw new CoreException(' خطا در ثبت اطلاعات crm');
        }

        $user = $this->create($request->all());
        $user->status = User::STATUS_INACTIVE;
        $user->approve = 0;

        $user->crm_registered = $crmRegistered;
        if (!empty($referralId)) {
            $user->referral_id = $referralId;
        }

        $user->save();
        if ($request->has('introduction_id')) {
            $introducerCode = IntroducerCode::where('code', $request->introduction_id)
                ->where('status', 'active')
                ->first();

            if (isset($introducerCode))
                $introducerCode->introducer_code_id = $request->introduction_id;
        }


        if ($request->categories)
            $user->categories()->attach($request->categories);

        if ($request->price_classes)
            $user->PriceClasses()->sync($request->price_classes ?? NULL);

        $user->addresses()->create($request->all());
        $user->countries()->sync($request->country);
        $user->Provinces()->sync($request->province);
        $user->Cities()->sync($request->city);

        auth()->login($user);

        (new UserController())->sendSmsCode($user->mobile_number);

        $token = $user->createToken('Customer v1')->accessToken;
        $message = "";
        if ($user->mobile_number && ($user->first_name || $user->last_name))
            $message = trans('messages.api.customer.user.register');
        else
            $message = "اطلاعات مشتری سمت crm  ناقص می باشد لطفا با پشتیبانی تماس بگیرید";

        return [
            'status' => true,
            'user_id' => $user->id,
            'message' => $message,
            'access_token' => $token,
        ];
    }


    public function registerByVisitor(RegisterRequest $request)
    {
        if (!$request->introduction_id)
            throw new CoreException('کد معرف الزامیست');


        try {
            CrmSabz::_()->checkCorectData($request);
            $checkMobilePhone = CrmSabz::_()->checkMobilePhone($request->mobile_number, !empty($request->phone_number) ? $request->phone_number : null);
            if ($checkMobilePhone == true) {
                return [
                    'status' => true,
                    'message' => trans('messages.api.company.customer.store'),
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => true,
                'message' => trans('پاسخی از crm  دریافت نشد'),
            ];
        }



        $referralId = '';
        $storeCrm = CrmSabz::_()->storeCrm('create', 'app', $request, '');
        if ($storeCrm) {
            $crmRegistered = 1;
            $referralId = $storeCrm->id;
        } else {
            $crmRegistered = -1;
            throw new CoreException(' خطا در ثبت اطلاعات crm');
        }

        $user = $this->create($request->all());
        $user->status = User::STATUS_INACTIVE;
        $user->approve = 0;

        if (!empty($referralId)) {
            $user->referral_id = $referralId;
        }

        //  if ($request->introduction_id) {

        $user->introducer_code_id = $request->introduction_id;
        //  }
        $user->save();



        if ($request->categories)
            $user->categories()->attach($request->categories);

        if ($request->price_classes)
            $user->PriceClasses()->sync($request->price_classes ?? NULL);

        $user->addresses()->create($request->all());
        $user->countries()->sync($request->country);
        $user->Provinces()->sync($request->province);
        $user->Cities()->sync($request->city);
        $user->Area()->sync($request->area);

        // auth()->login($user);

        // (new UserController())->sendSmsCode($user->mobile_number);

        //   $token = $user->createToken('Customer v1')->accessToken;

        $message = "";
        if ($user->mobile_number && ($user->first_name || $user->last_name))
            $message = trans('messages.api.customer.user.register');
        else
            $message = "اطلاعات مشتری سمت crm  ناقص می باشد لطفا با پشتیبانی تماس بگیرید";
        return [
            'status' => true,
            'user_id' =>  $user->id,
            'message' => $message,
            //'access_token' => $token,
        ];
    }


    public function getArea(Request $request)
    {
        $result = array();
        $city = City::with('Areas')->where('id', $request->city_id)->first();
        foreach ($city->areas as $area) {
            $result[] = [
                "id" => $area['id'],
                "name" => $area['area']
            ];
        }
        return $result;
    }
}
