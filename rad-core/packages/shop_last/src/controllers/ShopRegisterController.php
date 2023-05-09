<?php

namespace core\Packages\shop\src\controllers;

use App\SMS;
use Exception;
// use App\Models\User\User;
use Carbon\Carbon;
use Lcobucci\JWT\Parser;
use App\Models\Setting\City;
use Illuminate\Http\Request;
use Core\System\Helper\CrmSabz;
use App\Events\User\SendSMSEvent;
use App\Models\User\SmsValidation;
use App\Models\User\IntroducerCode;
use App\Http\Controllers\Controller;
use App\Models\User\OneSignalPlayer;
use Illuminate\Support\Facades\Hash;
use Core\Packages\shop\Users as user;
use Illuminate\Support\Facades\Crypt;
use Core\System\Exceptions\CoreException;
use Core\System\Http\Controllers\CoreController;
use App\Http\Requests\api\Customer\v1\User\RegisterRequest;
use App\Http\Controllers\api\Customer\v1\User\UserController;
use App\Http\Requests\api\Customer\v1\User\customRegisterRequest;

class ShopRegisterController extends CoreController
{

    public function register(Request $request)
    {
        // try {
        //     CrmSabz::_()->checkCorectData($request);
        //     $checkMobilePhone = CrmSabz::_()->checkMobilePhone($request->mobile_number, !empty($request->phone_number) ? $request->phone_number : null);
        //     if ($checkMobilePhone == true) {
        //         return [
        //             'status' => true,
        //             'message' => trans('messages.api.company.customer.store'),
        //         ];
        //     }
        // } catch (Exception $e) {

        //     return [
        //         'status' => true,
        //         'message' => trans('پاسخی از crm  دریافت نشد'),
        //     ];
        // }



        // $referralId = '';
        // $storeCrm = CrmSabz::_()->storeCrm('create', 'app', $request, '');
        // if ($storeCrm) {
        //     $crmRegistered = 1;
        //     $referralId = $storeCrm->id;
        // } else {
        //     $crmRegistered = -1;
        //     throw new CoreException(' خطا در ثبت اطلاعات crm');
        // }


            if (count(User::where('mobile_number' , $request->mobile_number)->get()) == 0  && $request->mobile_number != null){



                $user = new User();
                $user->mobile_number = $request->mobile_number;
                $user->status = User::STATUS_INACTIVE;
                $user->password = hash::make('3x+1/2') ;
                $user->group_id = '47';
                $user->approve = 0;
                $user->kind  = User::KIND_CONSUMER ;


                // $user->crm_registered = $crmRegistered;
                // if (!empty($referralId)) {
                //     $user->referral_id = $referralId;
                // }

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

                // $user->addresses()->create($request->all());
                // $user->countries()->sync($request->country);
                // $user->Provinces()->sync($request->province);
                //this lines is error for register user,I commit it
                // $user->Areas()->sync($request->area);
                // $user->Routes()->sync($request->route);
                // $user->Cities()->sync($request->city);

                // auth()->login($user);
            }
                // (new UserController())->sendSmsCode($user->mobile_number);
              $sms =  $this->sendSmsCode($request->mobile_number);
              return $sms;

                // $token = $user->createToken('Customer v1')->accessToken;
                // $message = "";
                // if ($user->mobile_number && ($user->first_name || $user->last_name))
                //     $message = trans('messages.api.customer.user.register');
                // else
                //     $message = "اطلاعات مشتری سمت crm  ناقص می باشد لطفا با پشتیبانی تماس بگیرید";

                // return [
                //     'status' => true,
                //     'user_id' => $user->id,
                //     'message' =>    $message ,
                //     'access_token' => $token,
                // ];




            // }

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

















    public function  sendSmsCode($mobileNumber)
    {
        /** @var SmsValidation $smsValidation */
        $smsValidation = SmsValidation::where('mobile_number', $mobileNumber)
            ->latest()
            ->first();

        if ($smsValidation && $smsValidation->created_at->diffInSeconds(Carbon::now()) < 60) {
            return [
                'status'  => false,
                'message' => 'برای ارسال مجدد پیام ۶۰ ثانیه صبر کنید.',
            ];
        }

        $sms                = new SmsValidation();
        $sms->code          = rand(1000, 9999);
        $sms->kind          = SmsValidation::KIND_CONFIRM;
        $sms->mobile_number = $mobileNumber;
        $sms->save();

        // $message = str_replace(
        //     [':code'],
        //     [$sms->code],
        //     Setting::getSettingBy(Setting::SMS_CODE)
        // );
        $message =  "کد ورود شما به فروشگاه پروشا :" . $sms->code ;

        event(new SendSMSEvent($message, $mobileNumber));

        return [
            'status'  => true,
            'message' => 'پیام با موفقیت ارسال شد.',
        ];
    }














    public function checkSmsCode(Request $request )
    {
        /** @var SmsValidation $smsValidation */
        $smsValidation = SmsValidation::where([
            'mobile_number' =>$request->mobile_number ,
            'code'          => $request->code,
        ])->first();
// dd($smsValidation-);
        // dd( SmsValidation::where('mobile_number' ,ltrim(auth('mobile')->user()->mobile_number , '0'))->where('code' , $request->code)->first() );
        // dd( ltrim(auth('mobile')->user()->mobile_number , '0'));


        if (!$smsValidation) {
            return [
                'status'  => false,
                'message' => 'کد ارسالی صحیح نمی‌باشد.',
            ];
        }

        // $smsValidation->delete();


        /** @var User $customer */
        $customer                             = User::where('mobile_number', $request->mobile_number)->first();
        $customer->mobile_number_confirmation = true;
        $customer->save();


        // if (!$customer->introducer_code_id) {
        //     $value = $request->bearerToken();
        //     $id    = (new Parser())->parse($value)->getHeader('jti');
        //     $token = $request->user()->tokens->find($id);
        //     $token->revoke();

        //     return [
        //         'status'  => true,
        //         'message' => 'شماره موبایل شما تایید شد. پشتیبانی ما با شما تماس می‌گیرد.',
        //     ];
        // }
        // dd($customer->password);

        $customer->status = User::STATUS_ACTIVE;
        $customer->save();


            $user = User::_()->login([
                "email" => $request->mobile_number,
                "password" =>  '',
                "log_in_with_mobile_only" => '1'

            ]);

        // $token = $customer->createToken('Customer v1')->accessToken;

        return $this->responseHandler2($user);

    }

    public function attachSignalPlayerId(Request $request)
    {
        \Log::info($request->toArray());

        $provider = 'fcm';
        if ($request->provider == 'chabokpush')
            $provider = $request->provider;

        /** @var User $customer */
        $customer = auth('mobile')->user();

        $SignalPlayer = OneSignalPlayer::where('player_id', $request->player_id)
            ->where('provider', $provider)
            ->first();

        if (!$SignalPlayer)
            $SignalPlayer = new OneSignalPlayer();

        $SignalPlayer->player_id = $request->player_id;
        $SignalPlayer->provider = $provider;
        $SignalPlayer->User()->associate($customer);
        $SignalPlayer->save();

        return [
            'status' => true,
            'id'     => $SignalPlayer->id,
        ];
    }
}
