<?php

namespace App\Http\Controllers\api\Customer\v1\User;

use App\Events\User\SendSMSEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\api\Customer\v1\User\UpdateUserRequest;
use App\Http\Requests\api\v1\Customer\User\checkSmsCodeRequest;
use App\Models\Setting\Setting;
use App\Models\User\Address;
use App\Models\User\OneSignalPlayer;
use App\Models\User\SmsValidation;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Lcobucci\JWT\Parser;

class UserController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function show()
    {
        if (auth('mobile')->user()->isActive()) {
            $user = User::with([
                'addresses',
                'countries',
                'Provinces',
                'Cities',
                'Photo',
            ])->findOrFail(auth()->id());

            return $user;
        }

        return response([
            'status'  => false,
            'message' => trans('messages.api.customer.user.inactive_user'),
        ])->setStatusCode(401);
    }

    /**
     * @param UpdateUserRequest $request
     *
     * @return array
     */
    public function update(UpdateUserRequest $request)
    {
        /** @var User $user */
        $user = auth('mobile')->user();

        if ($request->email)
            $user->email = $request->email;

        //		if ($request->mobile_number)
        //			$user->mobile_number = $request->mobile_number;

        if ($request->first_name)
            $user->first_name = $request->first_name;

        if ($request->last_name)
            $user->last_name = $request->last_name;

        if ($request->photo_id)
            $user->photo_id = $request->photo_id;


        if ($request->photo_id==null)
            $user->photo_id =NULL;


        if ($request->password)
            $user->password = bcrypt($request->password);
        $user->save();

        // Store Addresses
        if (isset($user->addresses[0]) && $user->addresses[0]) {
            $addressEntity = $user->addresses[0];
        } else {
            $addressEntity = new Address();
        }
        if (isset($request->address))
            $addressEntity->address     = $request->address;
        if (isset($request->postal_code))
            $addressEntity->postal_code = $request->postal_code;
        if (isset($request->lat))
            $addressEntity->lat         = $request->lat;
        if (isset($request->long))
            $addressEntity->long        = $request->long;
        $addressEntity->User()->associate($user);
        $addressEntity->save();

        return [
            'status'  => true,
            'message' => trans('messages.api.customer.user.update_success'),
        ];
    }

    public function requestSmsCode(Request $request)
    {
        $mobileNumber = $request->mobile_number;

        return $this->sendSmsCode($mobileNumber);
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

        $message = str_replace(
            [':code'],
            [$sms->code],
            Setting::getSettingBy(Setting::SMS_CODE)
        );
        event(new SendSMSEvent($message, $mobileNumber));

        return [
            'status'  => true,
            'message' => 'پیام با موفقیت ارسال شد.',
        ];
    }


    public function checkSmsCodeNew(checkSmsCodeRequest $request)
    {
        /** @var SmsValidation $smsValidation */
        $smsValidation = SmsValidation::where([
            'mobile_number' => $request->mobile_number,
            'code'          => $request->code,
        ])->first();

        if (!$smsValidation) {
            return [
                'status'  => false,
                'message' => 'کد ارسالی صحیح نمی‌باشد.',
            ];
        }

        $smsValidation->delete();


        /** @var User $customer */
        $customer                             = User::where('mobile_number', $request->mobile_number)->first();
        $customer->mobile_number_confirmation = true;
        $customer->approve = 1;
        $customer->save();

        if (!$customer->introducer_code_id) {
            $value = $request->bearerToken();
            $id    = (new Parser())->parse($value)->getHeader('jti');
            $token = $request->user()->tokens->find($id);
            $token->revoke();

            return [
                'status'  => true,
                'message' => 'شماره موبایل شما تایید شد. پشتیبانی ما با شما تماس می‌گیرد.',
            ];
        }

        $customer->status = User::STATUS_ACTIVE;
        $customer->save();

        $token = $this->getCustomersAccessToken($customer->id);
        return [
            'status'       => true,
            'message'      => 'فعال سازی با موفقیت انجام شد.',
            'access_token' => $token,
        ];
    }


    public function checkSmsCode(Request $request)
    {
        /** @var SmsValidation $smsValidation */
        $smsValidation = SmsValidation::where([
            'mobile_number' => auth('mobile')->user()->mobile_number,
            'code'          => $request->code,
        ])->first();

        if (!$smsValidation) {
            return [
                'status'  => false,
                'message' => 'کد ارسالی صحیح نمی‌باشد.',
            ];
        }

        $smsValidation->delete();


        /** @var User $customer */
        $customer                             = User::where('mobile_number', auth('mobile')->user()->mobile_number)->first();
        $customer->mobile_number_confirmation = true;
        $customer->save();

        if (!$customer->introducer_code_id) {
            $value = $request->bearerToken();
            $id    = (new Parser())->parse($value)->getHeader('jti');
            $token = $request->user()->tokens->find($id);
            $token->revoke();

            return [
                'status'  => true,
                'message' => 'شماره موبایل شما تایید شد. پشتیبانی ما با شما تماس می‌گیرد.',
            ];
        }

        $customer->status = User::STATUS_ACTIVE;
        $customer->save();

        $token = $customer->createToken('Customer v1')->accessToken;

        return [
            'status'       => true,
            'message'      => 'فعال سازی با موفقیت انجام شد.',
            'access_token' => $token,
        ];
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

    public function getCustomersAccessToken($user_id)
    {
        $d = [];
        $user = Auth::loginUsingId($user_id);
        if (!empty($user)) {
            $token = JWTAuth::fromUser($user);
            if (!empty($token)) {
                $result = Users::_()->createNewToken($token, $user);
                if (!empty($result)) {

                    $result->result['access_token'];
                }
            }
        }
        return $result;
    }
}
