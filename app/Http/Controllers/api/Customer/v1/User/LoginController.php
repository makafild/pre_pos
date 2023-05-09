<?php

namespace App\Http\Controllers\api\Customer\v1\User;

use Carbon\Carbon;
use Lcobucci\JWT\Parser;
use App\Models\User\User;
use Illuminate\Http\Request;
use Core\Packages\group\Group;
use App\Models\Setting\Setting;
use App\Services\IranSMSService;
use App\Models\User\SmsValidation;
use App\Http\Controllers\Controller;
use App\Models\User\OneSignalPlayer;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\api\Customer\v1\User\LoginRequest;
use App\Http\Requests\api\v1\Customer\User\forgetRequest;
use App\Http\Requests\api\v1\Customer\User\checkForgetSmsCode;
use App\Http\Requests\api\v1\Customer\User\checkSmsCodeRequest;
use Core\Packages\customer\Loglogin;
use Core\Packages\order\Visi;

class LoginController extends Controller
{



    public function login(LoginRequest $request)
    {


        $field = 'email';

        if (filter_var($request->input('email'), FILTER_VALIDATE_EMAIL)) {
            $field = 'email';
        } elseif (is_numeric($request->input('email'))) {

            $field = 'mobile_number';
        }

        $request->merge([
            $field => $request->input('email'),
        ]);
        if (!auth('mobile')->attempt($request->only([$field, 'password']))) {
            return [
                'status'       => false,
                'message'      => trans('نام کاربری یا رمز عبور اشتباه است'),
                'access_token' => NULL,
            ];
        }

        if (auth('mobile')->user()->isInactive()) {
            return [
                'status'       => false,
                'message'      => trans('اکانت شما غیر فعال میباشد'),
                'access_token' => NULL,
            ];
        }
        $token = auth('mobile')->refresh();

        if ($request->kind == 'customer') {
            $result = Visi::where('user_id', auth('mobile')->user()->id)->first();
            if ($result)
                return [
                    'status'       => true,
                    'message'      => trans('نوع شما باید مشتری باشد'),
                    'access_token' => NULL,
                ];
        } else {
            $result = Visi::where('user_id', auth('mobile')->user()->id)->first();
            if (!$result)
                return [
                    'status'       => true,
                    'message'      => trans('نوع شما باید ویزیتور باشد'),
                    'access_token' => NULL,
                ];
        }

        if (auth('mobile')->user()->group_id)
            $group = Group::find(auth('mobile')->user()->group_id); //where('id',auth('api')->user()['group_id'])->first();
        else
            $group = null;
        $list_urls = ($group) ? json_decode($group->access) : array();

        Loglogin::updateOrCreate([ "user_id" => auth('mobile')->user()->id],
            [
                "user_id" => auth('mobile')->user()->id,
                "created_at" => now()
            ]
        );

        return [
            'status'       => true,
            'user_id'      => auth('mobile')->user()->id,
            'user'      => auth('mobile')->user(),
            'message'      => trans('messages.api.customer.user.login_success'),
            'access_token' => $token,
            'permissions' => $list_urls,
        ];
    }

    public function forgetRequest(forgetRequest $request)
    {
        /** @var SmsValidation $smsValidation */
        $smsValidation = SmsValidation::where('mobile_number', $request->mobile_number)
            ->orderBy('created_at')
            ->first();

        if ($smsValidation && $smsValidation->created_at->diffInSeconds(Carbon::now()) < 60) {
            return [
                'status'  => false,
                'message' => 'برای ارسال مجدد پیام ۶۰ ثانیه صبر کنید.',
            ];
        }

        $sms = new SmsValidation();
        $sms->code = rand(1000, 9999);
        $sms->kind = SmsValidation::KIND_FORGET;
        $sms->mobile_number = $request->mobile_number;
        $sms->save();

        $message = str_replace(
            [':code'],
            [$sms->code],
            Setting::getSettingBy(Setting::SMS_CODE)
        );
        IranSMSService::send($sms->code, $request->mobile_number);

        return [
            'status'  => true,
            'message' => 'پیام با موفقیت ارسال شد.',
        ];
    }


    public function checkForgetSmsCode(checkForgetSmsCode $request)
    {
        /** @var SmsValidation $smsValidation */
        $smsValidation = SmsValidation::where([
            'mobile_number' => $request->mobile_number,
            'code'          => $request->code,
        ])->first();

        if (!$smsValidation) {
            return [
                'status'  => true,
                'message' => 'کد ارسالی صحیح نمی‌باشد.',
            ];
        }

        $smsValidation->delete();

        $user = User::where([
            'mobile_number' => $request->mobile_number,
        ])->first();

        $user->password = bcrypt($request->password);
        $user->save();

        auth()->login($user);
        $token = $user->createToken('Customer v1')->accessToken;

        return [
            'status'       => true,
            'user_id'      => $user->id,
            'message'      => 'کلمه عبور با موفقیت به روزرسانی شد. ',
            'access_token' => $token,
        ];
    }

    public function logout(Request $request)
    {
        OneSignalPlayer::where('id', $request->onesignal_id)->delete();

        //		$token = $request->bearerToken();
        //		if ($token) {
        //
        //			$id = (new Parser())->parse($token)->getHeader('jti');
        //			$revoked = \DB::table('oauth_access_tokens')
        //				->where('id', '=', $id)
        //				->update(['revoked' => 1]);
        //		}
        //		auth::logout();

        return [
            'status'  => true,
            'message' => 'با موفقیت خارج شدید.',
        ];
    }
}
