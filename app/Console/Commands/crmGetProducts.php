<?php

namespace App\Console\Commands;

use App\Models\Product\Product;
use App\Models\Setting\Constant;
use Core\System\Helper\CrmSabz;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Core\Packages\user\Users;
use Core\Packages\user\Address;
use Core\Packages\customer\CompanyCustomer;
use Illuminate\Support\Facades\Log;

class crmGetProducts extends Command
{

    protected $signature = 'crmGetProducts:start';

    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function prepareData($type, $row, $findRec,$brand='279640036')
    {
        $categories = CrmSabz::_()->crmToMasterActivityCategory();
        $units = CrmSabz::_()->crmToMasterUnits();
        $brands = CrmSabz::_()->crmToMasterBrands();
        $unitId = '';
        $brandId = $brands[$brand];

//        if (!is_null($row->unitID)) {
//            if (isset($units[$row->unitID])) {
//                $unitId = $units[$row->unitID];
//            } else {
//                Log::channel('crm')->info('GET' . ' unit PRODUCT : ' . $row->unitID . ' info : ' . json_encode($row));
//                return;
//            }
//        }
//
//        if (!is_null($row->source)) {
//            if (isset($brands[$row->source])) {
//                $brandId = $brands[$row->source];
//            } else {
//                Log::channel('crm')->info('GET' . ' brand PRODUCT : ' . $row->source . ' info : ' . json_encode($row));
//                return;
//            }
//        }
        $logApiCrmData = [
            'mobile' => '',
            'phone' => '',
            'output' => json_encode($row),
            'referral_id' => $row->productId,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];
        $data = [
            'api_service' => 'crm_sabz',
            'referral_id' => $row->productId,
            'serial' => $row->productNumber,
            'number_of_page' => $row->numberOfBookPages,
            'isbn' => $row->isbn,
            'weight' => $row->weight,
//            'master_unit_id' => $unitId,//todo
            'master_unit_id' => 751,//todo
            'description' => $row->description,
            'sales_price' => $row->fee,
            'consumer_price' => $row->fee,
            'name_fa' => $row->productName,
            'brand_id' => $brandId,//todo
            'product_type_2' => $row->genre,
            'company_id' => 7908,
            'product_id' => random_int(1000000000, 9999999999),
            'sublayer_id' => 748,
            'order_column' => 1,
        ];

        if ($type == 'create') {
            $product = Product::create($data);
            $logApiCrmData['type'] = 'get-product-create';
        }

        if ($type == 'update') {
            $product = Product::find($findRec['id']);
            Product::where('id', $findRec['id'])->update($data);
            $logApiCrmData['type'] = 'get-product-update';
        }

       $customer_categores=Constant::where('kind','customer_category')->pluck('id');
        $product->UserCategories()->sync($customer_categores);

        $logApiCrmData['product_id'] = $product->productId;
        DB::table('log_api_crm')->insert($logApiCrmData);

    }

    public function handle()
    {

        $brands=CrmSabz::_()->crmToMasterBrands();
        $brands=array_keys($brands);
        foreach($brands as $brand){
        $rows = CrmSabz::_()->get('product',$brand);
        if (isset($rows->isSucceed) && $rows->isSucceed) {

            $i = 1;
            Log::channel('crm')->info('@@EXECUTE PRODUCT@@');

            foreach ($rows->content as $row) {

                $findRec = Product::where('referral_id', $row->productId)->first();
                $i++;
                if (empty($findRec)) {
                    try {
                        $this->prepareData('create', $row, $findRec,$brand);
                    } catch (\Exception $e) {
                        Log::channel('crm')->info('GET' . 'CREATECatch PRODUCT : ' . $e->getMessage());
                    }
                } else {
                    try {

                        // if (
                        //     Carbon::createFromTimestamp(strtotime($row->modifiedOn))->timezone('Asia/Tehran')->format('Y-m-d H:i:s') >
                        //     Carbon::now('Asia/Tehran')->format('Y-m-d H:i:s')
                        // ) {

                            $this->prepareData('update', $row, $findRec,$brand);
                      //  }
                    } catch (\Exception $e) {
                        Log::channel('crm')->info('GET' . 'UPDATECatch PRODUCT : ' . $e->getMessage());
                    }
                }


            }
        } else {
            Log::channel('crm')->info('GET' . 'DISCONNECT!!!!!');
            $command = "openfortivpn -c /etc/openfortivpn/config";
            $returnVar = NULL;
            $output = NULL;
            exec($command, $output, $returnVar);
            //event(new SendSMSEvent('DISCONNECT!!!!!', '09128558939'));
        }
    }
    }
}
