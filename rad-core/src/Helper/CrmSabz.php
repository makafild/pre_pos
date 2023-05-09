<?php

namespace Core\System\Helper;

use App\Events\User\SendSMSEvent;
use App\Models\Setting\City;
use App\Models\Setting\Province;
use Core\Packages\customer\CompanyCustomer;
use Core\Packages\user\Address;
use Core\Packages\user\Users;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\BaseModel;
use App\Models\Setting\Setting;
use Illuminate\Support\Facades\Log;
use Core\System\Exceptions\CoreException;


class CrmSabz
{


    private static $_instance = null;

    private static $url = "http://retailapi.kheilisabz.com";
    //private static $url = "http://178.252.133.78";
    //    private static $url = "http://crmapi.testit.ir/Accounts/api/accounts";
    private $username = "admin";
    private $password = "123";

    protected $signature = 'master functions';


    public static function _()
    {
        if (self::$_instance == null) {
            self::$_instance = new CrmSabz();
        }
        return self::$_instance;
    }

    public function get($type='user',$brand='279640035')
    {
        if($type=='product'){
            $url=self::$url . "/api/products/".$brand;
        }else{
            $url=self::$url . "/api/accounts";
        }
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_USERPWD => $this->username . ":" . $this->password,
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET"
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }

    function post($type, $data, $attribute = [])
    {
        if ($type == 'customer') {
            $url = self::$url . "/api/accounts";
        }
        if ($type == 'address') {
            $url = self::$url . "/api/address/{$attribute['referralId']}/addresses";
        }
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_USERPWD => $this->username . ":" . $this->password,
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }

    function put($type, $data, $id)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_USERPWD => $this->username . ":" . $this->password,
            CURLOPT_URL => self::$url . "/api/accounts" . '/' . $id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }


    public function checkDuplicateInfo($mobile, $phone)
    {
        $phone = is_null($phone) ? 0 : $phone;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_USERPWD => $this->username . ":" . $this->password,
            CURLOPT_URL => self::$url . "/api/accounts/$phone/$mobile",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET"
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }

    function crmToMasterProvinces()
    {
        $provinces = [];
        $rows = Province::get();
        if (!empty($rows)) {
            foreach ($rows as $row) {
                $provinces[$row['bmsd_locationbaseId']] = $row['id'];
            }
        }
        return $provinces;
    }

    function crmToMasterCities()
    {
        $cities = [];
        $rows = City::get();
        if (!empty($rows)) {
            foreach ($rows as $row) {
                $cities[$row['bmsd_locationbaseId']] = $row['id'];
            }
        }
        return $cities;
    }

    function crmToMasterActivityCategory()
    {
        //ActivityCategory		دسته فعالیت
        //customer_category		زمینیه فعالیت
        return [
            '279640000' => '834', # 'اسباب بازی'
            '279640001' => '833', #'کتاب فروشی'
            '279640002' => '835', #'لوازم التحریر'
            '279640003' => '836', #'مولتی'
            '279640004' => '843', #'نامشخص'
            '279640005' => '1306', #'توی فکری'
            '279640006' => '1307', #'کتاب کودک و بزرگسال'
            '279640007' => '1308', #'کتوب تخصصی'
            '279640008' => '1309', #'کمک درسی و آموزشی'
            '279640009' => '1310',  #'رمان بزرگسال و کودک'
        ];
    }

    function masterToCrmActivityCategory()
    {
        //ActivityCategory		دسته فعالیت
        //customer_category		زمینیه فعالیت
        return [
            '834' => '279640000', # 'اسباب بازی'
            '833' => '279640001', #'کتاب فروشی'
            '835' => '279640002', #'لوازم التحریر'
            '836' => '279640003', #'مولتی'
            '843' => '279640004', #'نامشخص'
            '1306' => '279640005', #'توی فکری'
            '1307' => '279640006', #'کتاب کودک و بزرگسال'
            '1308' => '279640007', #'کتوب تخصصی'
            '1309' => '279640008', #'کمک درسی و آموزشی'
            '1310' => '279640009', #'رمان بزرگسال و کودک'
        ];
    }

    function crmToMasterInputSource()
    {
        return [
            '279640000' => '821', # 'سنسوس'
            '279640001' => '822', #'فایل ریتیل'
        ];
    }

    function masterToCrmInputSource()
    {
        return [
            '821' => '279640000', # 'سنسوس'
            '822' => '279640001', #'فایل ریتیل'
        ];
    }

    function crmToMasterCustomerGrade()
    {
        return [
            '279640000' => '807', # 'A'
            '279640001' => '808', #'B'
            '279640002' => '809', #'C'
            '279640003' => '810', #'D'
            '279640004' => '886', #'E'
            '279640005' => '885', #'بازار'
        ];
    }

    function masterToCrmCustomerGrade()
    {
        return [
            '807' => '279640000', # 'A'
            '808' => '279640001', #'B'
            '809' => '279640002', #'C'
            '810' => '279640003', #'D'
            '886' => '279640004', #'E'
            '885' => '279640005', #'بازار'
        ];
    }

    function crmToMasterCustomerGroup()
    {
        return [
            '279640000' => '811', # 'اسباب بازی-توی',
            '279640001' => '816', # 'لوازم التحریر',
            '279640002' => '817', # 'مولتی',
            '279640003' => '888', # 'توی و فکری',
            '279640004' => '813', # 'کتاب بزرگسال و کودک',
            '279640005' => '814', # 'کتاب دانشگاهی یا تخصصی',
            '279640006' => '815', # 'کمک درسی',
            '279640007' => '1305', #'رمان بزرگ سال و کودک'

        ];
    }

    function masterToCrmCustomerGroup()
    {
        return [
            '811' => '279640000', # 'اسباب بازی-توی',
            '816' => '279640001', # 'لوازم التحریر',
            '817' => '279640002', # 'مولتی',
            '888' => '279640003', # 'توی و فکری',
            '813' => '279640004', # 'کتاب بزرگسال و کودک',
            '814' => '279640005', # 'کتاب دانشگاهی یا تخصصی',
            '815' => '279640006', # 'کمک درسی'
            '1305' => '279640007', #'رمان بزرگ سال و کودک'

        ];
    }


    function crmToMasterCustomerClass()
    {
        return [
            '279640000' => '910', # 'اینترنتی',
            '279640001' => '911', # 'بازار',
            '279640002' => '912', # 'پخش',
            '279640003' => '913', # 'شهرکتاب',
            '279640004' => '914', # 'فروشگاه اصلی',
            '279640005' => '915', # 'فروشگاه محلی',
            '279640006' => '916', # 'مدرسه'
        ];
    }

    function masterToCrmCustomerClass()
    {
        return [
            '910' => '279640000', # 'اینترنتی',
            '911' => '279640001', # 'بازار',
            '912' => '279640002', # 'پخش',
            '913' => '279640003', # 'شهرکتاب',
            '914' => '279640004', # 'فروشگاه اصلی',
            '915' => '279640005', # 'فروشگاه محلی',
            '916' => '279640006', # 'مدرسه'
        ];
    }

    function masterToCrmProvinces()
    {
        $provinces = [];
        $rows = Province::get();

        if (!empty($rows)) {
            foreach ($rows as $row) {
                $provinces[$row['id']] = $row['bmsd_locationbaseId'];
            }
        }
        return $provinces;
    }

    function masterToCrmCities()
    {
        $cities = [];
        $rows = City::get();
        if (!empty($rows)) {
            foreach ($rows as $row) {
                $cities[$row['id']] = $row['bmsd_locationbaseId'];
            }
        }
        return $cities;
    }

    function crmToMasterUnits()
    {
        return [
            '4' => '751',# 'جلد',
        ];
    }

    function crmToMasterBrands()
    {
        return [
            '279640035' => '69',# 'الیپون',
            '279640036' => '70',# 'زینگو',
            '279640051' => '96',# 'خیلی سبز',
            '279640050' => '95',# 'منتشران',
        ];
    }

    public function checkCorectData($request)
    {
        $provinces = $this->masterToCrmProvinces();
        $cities = $this->masterToCrmCities();
        $categories = $this->masterToCrmActivityCategory();
        $inputSources = $this->masterToCrmInputSource();
        $customerGrade = $this->masterToCrmCustomerGrade();
        $customerGroup = $this->masterToCrmCustomerGroup();
        $customerClass = $this->masterToCrmCustomerClass();

        if (!empty($request->province) && !isset($provinces[$request->province])) {
            Log::channel('crm')->info('POST-PANEL => ' . 'province_id : ' . $request->province . ' info : ' . json_encode($request));
            throw new CoreException('فیلد استان مشتری سمت  crm وجود ندارد');
        }

        if (!empty($request->city) && !isset($cities[$request->city])) {
            Log::channel('crm')->info('POST-PANEL => ' . 'city_id : ' . $request->city . ' info : ' . json_encode($request));
            throw new CoreException('فیلد شهرستان مشتری سمت  crm وجود ندارد');
        }
        if (isset($request->customer_category['id'])) {
            if (!empty($request->customer_category['id']) && !isset($categories[$request->customer_category['id']])) {
                Log::channel('crm')->info('POST-PANEL => ' . 'category_id : ' . $request->customer_category['id'] . ' info : ' . json_encode($request));
                throw new CoreException('فیلد گروه مشتری سمت  crm وجود ندارد');
            }
        } else {
            if (!empty($request->categories) && !isset($categories[$request->categories])) {
                Log::channel('crm')->info('POST-PANEL => ' . 'category_id : ' . $request->categories . ' info : ' . json_encode($request));
                throw new CoreException('فیلد دسته فعالیت سمت  crm وجود ندارد');
            }
        }

        if (!empty($request->introduction_source) && !isset($inputSources[$request->introduction_source])) {
            Log::channel('crm')->info('POST-PANEL => ' . 'introduction_source : ' . $request->introduction_source . ' info : ' . json_encode($request));
            throw new CoreException('فیلد  منبع ورودی سمت crm وجود ندارد');
        }

        if (!empty($request->customer_grade) && !isset($customerGrade[$request->customer_grade])) {
            Log::channel('crm')->info('POST-PANEL => ' . 'customer_grade : ' . $request->customer_grade . ' info : ' . json_encode($request));
            throw new CoreException('فیلد گرید مشتری سمت  crm وجود ندارد');
        }

        if (!empty($request->customer_group) && !isset($customerGroup[$request->customer_group])) {
            Log::channel('crm')->info('POST-PANEL => ' . 'customer_group : ' . $request->customer_group . ' info : ' . json_encode($request));
            throw new CoreException('فیلد گروه مشتری سمت  crm وجود ندارد');
        }

        if (!empty($request->customer_class) && !isset($customerClass[$request->customer_class])) {
            Log::channel('crm')->info('POST-PANEL => ' . 'customer_class : ' . $request->customer_class . ' info : ' . json_encode($request));
            throw new CoreException('فیلد دسته بندی مشتری سمت  crm وجود ندارد');
        }
    }

    public function prepareData($type, $row, $findRec = '')
    {
        $provinces = CrmSabz::_()->crmToMasterProvinces();
        $cities = CrmSabz::_()->crmToMasterCities();
        $categories = CrmSabz::_()->crmToMasterActivityCategory();
        $inputSources = CrmSabz::_()->crmToMasterInputSource();
        $customerGrade = CrmSabz::_()->crmToMasterCustomerGrade();
        $customerGroup = CrmSabz::_()->crmToMasterCustomerGroup();
        $customerClass = CrmSabz::_()->crmToMasterCustomerClass();
        $inputSourcesField = null;
        $customerGradeField = null;
        $customerGroupField = null;
        $customerClassField = null;
        if (!is_null($row->inputSource)) {
            $inputSourcesField = $inputSources[$row->inputSource];
            if (!isset($inputSources[$row->inputSource])) {
                Log::channel('crm')->info('GET' . ' input_source : ' . $row->inputSource . ' info : ' . json_encode($row));
                throw new CoreException('فیلد  منبع ورودی سمت پنل وجود ندارد');
            }
        }

        if (!is_null($row->retailAccountGrade)) {
            $customerGradeField = $customerGrade[$row->retailAccountGrade];
            if (!isset($customerGrade[$row->retailAccountGrade])) {
                Log::channel('crm')->info('GET' . ' customer_grade : ' . $row->retailAccountGrade . ' info : ' . json_encode($row));
                throw new CoreException('فیلد گرید مشتری سمت  پنل وجود ندارد');
            }
        }

        if (!is_null($row->specializedFieldOFactivity)) {
            $customerGroupField = $customerGroup[$row->specializedFieldOFactivity];
            if (!isset($customerGroup[$row->specializedFieldOFactivity])) {
                Log::channel('crm')->info('GET' . ' customer_group : ' . $row->specializedFieldOFactivity . ' info : ' . json_encode($row));
                throw new CoreException('فیلد گروه مشتری سمت  پنل وجود ندارد');
            }
        }

        if (!is_null($row->retailAccountType)) {
            $customerClassField = $customerClass[$row->retailAccountType];
            if (!isset($customerClass[$row->retailAccountType])) {
                Log::channel('crm')->info('GET' . ' customer_class : ' . $row->retailAccountType . ' info : ' . json_encode($row));
                throw new CoreException('فیلد دسته بندی مشتری سمت  پنل وجود ندارد');
            }
        }



        if (!isset($categories[$row->activityCategory])) {
            Log::channel('crm')->info('GET' . ' category_id : ' . $row->activityCategory . ' info : ' . json_encode($row));
            throw new CoreException('فیلد  زمینه فعالیت سمت  پنل وجود ندارد');
        }


        if (!empty($row->addresses)) {
            foreach ($row->addresses as $address) {

                if (!isset($provinces[$address->province->id])) {
                    Log::channel('crm')->info('GET' . 'province_id : ' . $address->province->id . ' info : ' . json_encode($row));
                    throw new CoreException('فیلد استان مشتری سمت  پنل وجود ندارد');
                }

                if (!isset($cities[$address->province->city->id])) {

                    Log::channel('crm')->info('GET' . 'city_id : ' . $address->province->city->id . ' info : ' . json_encode($row));
                    throw new CoreException('فیلد شهرستان مشتری سمت  پنل وجود ندارد');
                }
            }
        }


        $logApiCrmData = [
            'mobile' => $row->mobile,
            'phone' => $row->phone,
            'output' => json_encode($row),
            'referral_id' => $row->id,
            'created_at' => \Illuminate\Support\Carbon::now(),
            'updated_at' => Carbon::now()
        ];
        if ($type == 'create') {
            if (!empty(Users::where('referral_id', $row->id)->first())) {
                // return;
            }
           $user = Users::create([
                'referral_id' => $row->id,
                'store_name' => $row->name,
                'last_name' => $row->accountOwnerName,
                'mobile_number' => $row->mobile,
                'phone_number' => $row->phone,
                'introduction_source' => $inputSourcesField,
                'customer_grade' => $customerGradeField,
                'customer_group' => $customerGroupField,
                'customer_class' => $customerClassField,
                'kind' => 'customer',
                'status' => 'active',
                'crm_registered' => 2
            ]);

            CompanyCustomer::create([
                'referral_id' => $row->id,
                'store_name' => $row->name,
                'last_name' => $row->accountOwnerName,
                'company_id' => $user->id,
                'mobile_number' => $row->mobile,
                'phone_number' => $row->phone,
                'address' => json_encode($row->addresses)
            ]);
            if (!empty($row->addresses)) {
                foreach ($row->addresses as $address) {
                    if ($address->addressType == 1) { //Main Address
                        Address::create([
                            'address' => $address->postalAddress,
                            'postal_code' => $address->postalCode,
                            'user_id' => $user->id,
                        ]);

                        if (!empty($address->province)) {
                            $provinceId = $provinces[$address->province->id];
                            $user->Provinces()->sync([$provinceId]);

                            if (!empty($address->province->city)) {
                                $cityId = $cities[$address->province->city->id];
                                $user->Cities()->sync([$cityId]);
                            }
                        }
                    }
                }
            }

            $logApiCrmData['type'] = 'get-user-create';
        }

        //        if ($type == 'update') {
        //            $user = Users::find($findRec['id']);
        //
        //            Users::where('id', $findRec['id'])->update([
        //                'last_name' => $row->accountOwnerName,
        //        'store_name' => $row->name,
        //                'introduction_source' => $inputSourcesField,
        //                'customer_grade' => $customerGradeField,
        //                'customer_group' => $customerGroupField
        //                'customer_class' => $customerClassField
        //            ]);
        //
        //            CompanyCustomer::where('referral_id', $row->id)->update([
        //                'last_name' => $row->accountOwnerName,
        //        'store_name' => $row->name,
        //                'address' => json_encode($row->addresses)
        //            ]);
        //
        //            if (!empty($row->addresses)) {
        //                foreach ($row->addresses as $address) {
        //                    if ($address->addressType == 1) {//Main Address
        //                        Address::where('user_id', $findRec['id'])->update([
        //                            'address' => $address->postalAddress,
        //                            'postal_code' => $address->postalCode
        //                        ]);
        //
        //                        if (!empty($address->province)) {
        //                                $provinceId = $provinces[$address->province->id];
        //                                $user->Provinces()->sync([$provinceId]);
        //
        //                            if (!empty($address->province->city)) {
        //                                    $cityId = $cities[$address->province->city->id];
        //                                    $user->Cities()->sync([$cityId]);
        //                            }
        //                        }
        //                    }
        //                }
        //            }
        //            $logApiCrmData['type'] = 'get-user-update';
        //        }

        $user->Categories()->sync($categories[$row->activityCategory]);

        $logApiCrmData['user_id'] = $user->id;
        DB::table('log_api_crm')->insert($logApiCrmData);
    }

    public function checkMobilePhone($mobile, $phone)
    {
        $checkMobilePhoneCrm = $this->checkDuplicateInfo($mobile, $phone);
        if (isset($checkMobilePhoneCrm->isSucceed) && $checkMobilePhoneCrm->isSucceed) {
            $this->prepareData('create', $checkMobilePhoneCrm->content);
            return true;
            //            if ($checkMobilePhoneCrm->content->mobile == $mobile && $checkMobilePhoneCrm->content->phone == $phone) {
            //                throw new CoreException('شماره موبایل و تلفن سمت crm وجود دارند');
            //            }
            //            if ($checkMobilePhoneCrm->content->mobile == $mobile) {
            //                throw new CoreException('شماره موبایل سمت crm وجود دارند');
            //
            //            }
            //            if ($checkMobilePhoneCrm->content->phone == $phone) {
            //                throw new CoreException(' تلفن سمت crm وجود دارند');
            //            }
        }
        return false;
    }

    public function storeCrm($type, $inputSource, $request, $userId, $referralId = '')
    {


        $logApiCrmData = [
            'mobile' => !empty($request->mobile_number) ? $request->mobile_number : '',
            'phone' => !empty($request->phone_number) ? $request->phone_number : '',
            //            'input' => json_encode($request->toArray()),
            'type' => 'post-user',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];

        if (!empty($userId)) {
            $logApiCrmData['user_id'] = $userId;
            $logApiCrmData['referral_id'] = $userId;
        }

        $provinces = $this->masterToCrmProvinces();
        $cities = $this->masterToCrmCities();
        $categories = $this->masterToCrmActivityCategory();
        $inputSources = $this->masterToCrmInputSource();
        $customerGrade = $this->masterToCrmCustomerGrade();
        $customerGroup = $this->masterToCrmCustomerGroup();
        $customerClass = $this->masterToCrmCustomerClass();

        if ($inputSource == 'panel') {
            $logApiCrmData['type'] = $logApiCrmData['type'] . '-panel';
        } else {
            $logApiCrmData['type'] = $logApiCrmData['type'] . '-app';
        }

        if ($type == 'create') {
            $logApiCrmData['type'] = $logApiCrmData['type'] . '-create';
        } else {
            $logApiCrmData['type'] = $logApiCrmData['type'] . '-update';
        }

        if ($inputSource == 'panel') {
            $category = !empty($categories[$request->customer_category['id']]) ? $categories[$request->customer_category['id']] : '';
        } else {
            $category = !empty($categories[$request->categories]) ? $categories[$request->categories] : '';
        }


        $data = [
            'name' => !empty($request->store_name) ? $request->store_name : '',
            'accountOwnerName' => $request->first_name . ' ' . $request->last_name,
            'phone' => !empty($request->phone_number) ? $request->phone_number : '',
            'mobile' => !empty($request->mobile_number) ? $request->mobile_number : '',
            'retailNationalID' => !empty($request->national_id) ? $request->national_id : '',
            'email' => !empty($request->email) ? $request->email : '',
            'inputSource' => !empty($inputSources[$request->introduction_source]) ? $inputSources[$request->introduction_source] : '279640000',
            'retailAccountGrade' => !empty($customerGrade[$request->customer_grade]) ? $customerGrade[$request->customer_grade] : '',
            'specializedFieldOFactivity' => !empty($customerGroup[$request->customer_group]) ? $customerGroup[$request->customer_group] : '',
            'retailAccountType' => !empty($customerClass[$request->customer_class]) ? $customerClass[$request->customer_class] : '279640000',
            'activityCategory' => $category
        ];
        $addressData = [];

        if ($request->addresses) {
            foreach ($request->addresses as $index => $address) {
                $addressData[] = [
                    'addressType' => $index + 1,
                    'postalAddress' => !empty($address['address']) ? $address['address'] : '',
                    'postalCode' => !empty($address['postal_code']) ? $address['postal_code'] : '',
                    'lat' => !empty($address['lat']) ? $address['lat'] : '',
                    'long' => !empty($address['long']) ? $address['long'] : '',
                    'province' => [
                        'id' => $provinces[$request['province']],
                        'city' => [
                            'id' => $cities[$request['city']]
                        ]
                    ]
                ];
            }
        } else {

            $addressData[] = [
                'addressType' => 1,
                'postalAddress' => !empty($request->address) ? $request->address : '',
                'postalCode' => !empty($request->postal_code) ? $request->postal_code : '',
                'lat' => !empty($request->lat) ? $request->lat : '',
                'long' => !empty($request->long) ? $request->long : '',
                'province' => [
                 'id' => $provinces[$request['province']],
                    'city' => [
                        'id' => $cities[$request['city']],
                    ]
                ]
            ];
        }


        if ($type == 'update') {
            //            dd($data);
        }

        $data['addresses'] = $addressData;
        $logApiCrmData['input'] = json_encode($data);
//	dd($data);
        $result = $type == 'create' ? $this->post('customer', $data) : $this->put('customer', $data, $referralId);
//dd(    $data,   $result);

        if (isset($result->isSucceed) && $result->isSucceed) {
            $logApiCrmData['output'] = json_encode($result->content);
            DB::table('log_api_crm')->insert($logApiCrmData);
            return $result->content;
        } else if (isset($result->isSucceed) && $result->isSucceed == false) {
            throw new CoreException("crm " . $result->message);
        } else {
            $msg = 'error in ' . $type . ' customer ' . $inputSource;
            $logApiCrmData['output'] = $msg . ' => ' . json_encode($result);
            DB::table('log_api_crm')->insert($logApiCrmData);
            //event(new SendSMSEvent('DISCONNECT!!!!!', '09128558939'));
            Setting::where('key', "VPNSTATUS")->update(['value' => 0]);
            return false;
        }
    }
}
