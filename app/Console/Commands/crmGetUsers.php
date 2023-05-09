<?php

namespace App\Console\Commands;

use App\Events\User\SendSMSEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Core\Packages\user\Users;
use Core\Packages\user\Address;
use Core\Packages\customer\CompanyCustomer;
use Illuminate\Support\Facades\Log;
use Core\System\Helper\CrmSabz;

class crmGetUsers extends Command
{

    protected $signature = 'crmGetUsers:start';

    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function prepareData($type, $row, $findRec)
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
            if (isset($inputSources[$row->inputSource])) {
                $inputSourcesField = $inputSources[$row->inputSource];
            } else {
                Log::channel('crm')->info('GET' . ' input_source : ' . $row->inputSource . ' info : ' . json_encode($row));
                return;
            }
        }


        if (!is_null($row->retailAccountGrade)) {
            if (isset($customerGrade[$row->retailAccountGrade])) {
                $customerGradeField = $customerGrade[$row->retailAccountGrade];
            } else {
                Log::channel('crm')->info('GET' . ' customer_grade : ' . $row->retailAccountGrade . ' info : ' . json_encode($row));
                return;
            }
        }

        if (!is_null($row->specializedFieldOFactivity)) {
            if (isset($customerGroup[$row->specializedFieldOFactivity])) {
                $customerGroupField = $customerGroup[$row->specializedFieldOFactivity];
            } else {
                Log::channel('crm')->info('GET' . ' customer_group : ' . $row->specializedFieldOFactivity . ' info : ' . json_encode($row));
                return;
            }
        }

        if (!is_null($row->retailAccountType)) {
            if (isset($customerClass[$row->retailAccountType])) {
                $customerClassField = $customerClass[$row->retailAccountType];
            } else {
                Log::channel('crm')->info('GET' . ' customer_class : ' . $row->retailAccountType . ' info : ' . json_encode($row));
                return;
            }
        }

        $logApiCrmData = [
            'mobile' => $row->mobile,
            'phone' => $row->phone,
            'output' => json_encode($row),
            'referral_id' => $row->id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];
        if ($type == 'create') {
//            $id=Users::latest()->first()['id']+1;
            $user = Users::create([
                'api_service' => 'crm_sabz',
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
            ]);

            CompanyCustomer::create([
                'referral_id' => $row->id,
                'last_name' => $row->name,
                'company_id' => $user->id,
                'mobile_number' => $row->mobile,
                'phone_number' => $row->phone,
                'address' => json_encode($row->addresses)
            ]);
            if (!empty($row->addresses)) {
                foreach ($row->addresses as $address) {
                    if ($address->addressType == 1) {//Main Address
                        Address::create([
                            'address' => $address->postalAddress,
                            'postal_code' => $address->postalCode,
                            'user_id' => $user->id,
                        ]);

                        if (!empty($address->province)) {
                            if (isset($provinces[$address->province->id])) {
                                $provinceId = $provinces[$address->province->id];
                                $user->Provinces()->sync([$provinceId]);
                            } else {
                                Log::channel('crm')->info('GET' . 'province_id : ' . $address->province->id . ' info : ' . json_encode($row));
                                return;
                            }

                            if (!empty($address->province->city)) {
                                if (isset($cities[$address->province->city->id])) {
                                    $cityId = $cities[$address->province->city->id];
                                    $user->Cities()->sync([$cityId]);
                                } else {
                                    Log::channel('crm')->info('GET' . 'city_id : ' . $address->province->city->id . ' info : ' . json_encode($row));
                                    return;
                                }
                            }
                        }
                    }

                }
            }

            $logApiCrmData['type'] = 'get-user-create';
        }

        if ($type == 'update') {
            $user = Users::find($findRec['id']);

            Log::info($user->id);
            Users::where('id', $findRec['id'])->update([
                'store_name' => $row->name,
                'last_name' => $row->accountOwnerName,
                'introduction_source' => $inputSourcesField,
                'customer_grade' => $customerGradeField,
                'customer_group' => $customerGroupField,
                'customer_class' => $customerClassField
            ]);

            CompanyCustomer::where('referral_id', $row->id)->update([
                'last_name' => $row->accountOwnerName,
                'address' => json_encode($row->addresses)
            ]);

            if (!empty($row->addresses)) {
                foreach ($row->addresses as $address) {
                    if ($address->addressType == 1) {//Main Address
                        Address::where('user_id', $findRec['id'])->update([
                            'address' => $address->postalAddress,
                            'postal_code' => $address->postalCode
                        ]);
                        Log::info($address->province);
                        Log::info($address->province->city);

                        if (!empty($address->province)) {
                            if (isset($provinces[$address->province->id])) {
                                $provinceId = $provinces[$address->province->id];
                                $user->Provinces()->sync([$provinceId]);
                            } else {
                                Log::channel('crm')->info('GET' . 'province_id : ' . $address->province->id . ' info : ' . json_encode($row));
                                return;
                            }

                            if (!empty($address->province->city)) {
                                if (isset($cities[$address->province->city->id])) {
                                    $cityId = $cities[$address->province->city->id];
                                    $user->Cities()->sync([$cityId]);
                                } else {
                                    Log::channel('crm')->info('GET' . 'city_id : ' . $address->province->city->id . ' info : ' . json_encode($row));
                                    return;
                                }
                            }
                        }
                    }
                }
            }
            $logApiCrmData['type'] = 'get-user-update';
        }

        if (isset($categories[$row->activityCategory])) {
            $user->Categories()->sync($categories[$row->activityCategory]);
        } else {
            Log::channel('crm')->info('GET' . ' category_id : ' . $row->activityCategory . ' info : ' . json_encode($row));
            return;
        }

        $logApiCrmData['user_id'] = $user->id;
        DB::table('log_api_crm')->insert($logApiCrmData);

    }


    public function handle()
    {
        $rows = CrmSabz::_()->get();

        if (isset($rows->isSucceed) && $rows->isSucceed) {

            $i = 1;
            Log::channel('crm')->info('@@EXECUTE@@');
            foreach ($rows->content as $row) {


                $findRec = Users::orWhere('mobile_number', $row->mobile)->orWhere('phone_number', $row->phone)->orWhere('referral_id', $row->id)->first();


                $i++;

                if (empty($findRec)) {
                    try {
                        $this->prepareData('create', $row, $findRec);
                    } catch (\Exception $e) {
                        Log::channel('crm')->info('GET' . 'CREATECatch PRODUCT : ' .$e->getMessage() );
                    }
                } /*else {
                    try {
                        if (
                            Carbon::createFromTimestamp(strtotime($row->modifiedOn))->timezone('Asia/Tehran')->format('Y-m-d H:i:s') >
                            Carbon::now('Asia/Tehran')->format('Y-m-d H:i:s')
                        ) {
                          //  $this->prepareData('update', $row, $findRec);
                        }
                    } catch (\Exception $e) {
                        Log::info('noooooo');
                        Log::channel('crm')->info('GET' . 'UPDATECatch : ' . $e->getMessage());
                    }
                }*/


            }
        } else {
            Log::channel('crm')->info('GET' . 'DISCONNECT!!!!!');
            Log::info("behzad_not_okay");
            $command = "openfortivpn -c /etc/openfortivpn/config";
            $returnVar = NULL;
            $output  = NULL;
            exec($command, $output, $returnVar);
          // event(new SendSMSEvent('DISCONNECT!!!!!', '09128558939'));

        }
    }
}
