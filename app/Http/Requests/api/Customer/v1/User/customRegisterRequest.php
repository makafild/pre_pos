<?php

namespace App\Http\Requests\api\Customer\v1\User;

use App\Models\Setting\Constant;
use App\Models\User\User;
use App\Rules\isMobile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class RegisterRequest
 *
 * @package App\Http\Requests\api\Customer\v1\User
 * @property string $first_name
 * @property string $last_name
 * @property string $mobile_number
 * @property string $email
 * @property string $password
 * @property string $password_confirmation
 * @property string $address
 * @property string $postal_code
 * @property string $long
 * @property string $lat
 * @property integer $country
 * @property integer $province
 * @property integer $city
 * @property integer $introduction_id
 * @property integer[] $categories
 * @property integer[] price_classes
 *
 */
class customRegisterRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'mobile_number' => [
                'required',
                'unique:users',
                new isMobile,
            ],
            'email' => 'nullable|email|unique:users',
            'password' => 'required|confirmed',
            'password_confirmation' => 'required',
            'address' => 'nullable|string',
            'postal_code' => 'nullable|integer',
            'long' => 'nullable',
            'lat' => 'nullable',
            'country' => 'required|integer|exists:countries,id',
            'province' => 'required|integer|exists:provinces,id',
            'city' => 'required|integer|exists:cities,id',
            'price_classes' => 'array',
            'price_classes.*' => [ Rule::exists('price_classes', 'id')],
            'categories' => [
                'required',
                Rule::exists('constants', 'id')
                    ->where('kind', Constant::CUSTOMER_CATEGORY),
            ],

            'introduction_id' => ['nullable',
                Rule::exists('introducer_codes', 'code')
                    ->where('status', User::STATUS_ACTIVE),
            ],
            'photo_id' => 'nullable|exists:files,id',
            'national_id' => 'numeric|digits:10',
            'phone_number' => 'nullable|numeric',
            'customer_group' => [ Rule::exists('constants', 'id')
                ->where('kind', \Core\Packages\common\Constant::CUSTOMER_GROUP)],
            'customer_grade' => ['nullable', Rule::exists('constants', 'id')
                ->where('kind', Constant::CUSTOMER_GRADE)],
        ];
    }
}
