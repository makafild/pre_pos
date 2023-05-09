<?php

namespace core\Packages\robots\src\controllers;

use Core\Packages\product\Product;
use Core\Packages\robots\CompanyToken;
use Core\System\Http\Controllers\CoreController;
use Core\Packages\robots\src\request\ProductsRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class RobotsController extends CoreController
{



      public function check(Request $request){
 

        if (!$request->token) return ["status" => false, "code" => 403];

        $company = CompanyToken::where('token', $request->token)->first();
        if (!$company) return ["status" => false, "code" => 403];

 return [
            "status" => true,
            "code" => 200
        ];

}
    public function getProduct(Request $request)
    {
        ini_set('memory_limit', '40M');
        ini_set('max_execution_time', '3600000');

        if (!$request->token && !$request->products) return ["status" => false, "code" => 403];
        $company = CompanyToken::where('token', $request->token)->first();
        if (!$company) return ["status" => false, "code" => 403];

        $company_id = $company->company_id;
        foreach ($request->products as $product_input) {
        try{
            if (!$product_input['name_fa']) continue;
            $product = Product::where('referral_id', $product_input['referral_id'])->where('company_id', $company_id)->first();
            $create = false;
            if (!$product) {
                $product = new Product();
                $create = true;
            }
            if ($create) $product->referral_id = $product_input['referral_id'];
            if ($create) $product->company_id  = $company_id;
            $product->order_column = "1";
            $product->product_id = random_int(1000000000, 9999999999);
            $product->name_fa = $product_input['name_fa'];
            $product->show_status = ($product_input['status'] == 1) ? 'active' : 'inactive';
            $product->status = ($product_input['stock'] > 0) ? 'available' : 'unavailable';
            $product->sales_price = $product_input['sales_price'];
            $product->consumer_price = $product_input['consumer_price'];
            $product->master_unit_id = $product_input['master_unit_id'];
            $product->slave_unit_id = $product_input['slave_id'];
            $product->slave2_unit_id = $product_input['slave2_id'];
            if ($product_input['brand_id'])
                $product->brand_id = $product_input['brand_id'];
            if ($product_input['category_id'])
                $product->category_id = $product_input['category_id'];
            $product->per_master = ($product_input['per_master'])?$product_input['per_master']:0;
            $product->per_slave =($product_input['per_slave'])?$product_input['per_slave']:0;
            $product->master_status = ($product_input['master_status'] == 'active') ? 1 : 0;
            $product->slave_status = ($product_input['slave_status'] == 'active') ? 1 : 0;
            $product->slave2_status = ($product_input['slave2_status'] == 'active') ? 1 : 0;
            if ($product_input['class'])
            $product->UserCategories()->sync($product_input['class']);
            $product->save();
        }catch (Throwable $e) {
            if($status_sms)
             event(new SendSMSEvent(" دیتای مپ شده ی شرکت ". $company_id." برای محصول  ".$product_input['referral_id']." اشتباه میباشد  ", "09331014716"));
         $status_sms=false;
         }
            
        }
        return [
            "status" => true,
            "code" => 200
        ];
    }


    public function searchUser(Request $request)
    {



        $company = CompanyToken::where('company_id', auth('api')->user()->company_id)->first();
        if (!$company || !auth('api')->user()->api_url) return ["status" => false, "code" => 403, "message" => 'لایسنس یا api url ندارد'];



        $search = ($request->search) ? $request->search : NULL;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => auth('api')->user()->api_url . '/customer',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
    "token":"' . $company->token . '",
    "search":"' . $search . '"
}',
            CURLOPT_HTTPHEADER => array(
                'accept: application/json, text/plain, /',
                'origin: ',
                'Content-Type: application/json',
                'Cookie: XSRF-TOKEN=eyJpdiI6InVta3BHckJhMFRMM3FzbU5BVXNCTVE9PSIsInZhbHVlIjoiUmxQYkYxZFI5a2xGVURUS0dzVzlUZDhJaGtLYUdwVzlkOUkveUJFdXgxVlZYeEtNOTdGdzA3WXdZWU15T09laWVvQ0F4cVRlY0dlZHk2Y3luY2VSdFNSeHlsZEV0bEJiclVrcUFlNEZPUzdBbHhDVW5IdWdseTFTRUVta1hzek4iLCJtYWMiOiI1MDExZmM4YWFmZWVjN2Q4NTU5NTNiZThhNGE5ZjVmYTI3MjY5YWEyMDk1YzVjNWI3NmY4N2JlZTFhZjQ5MWFlIiwidGFnIjoiIn0%3D; laravel_session=eyJpdiI6IklnMjNlMExVWkFOS01STmxTZzV2enc9PSIsInZhbHVlIjoiU2pjTG8wMWJWNkNJSEp4RUVpRnROQURwbTlpTW12UkFaUmdaN2xDRndaVXJvdHlZZEtaOEhQcGJ6bEZrMlc0b280S3AzV2lubXVpandHWFVKQUt0WHlFaUxKNkthYXN2VGc0M2JsclpyUGVpQmh0eGo2bHFDbWZkWVRUZGYvNm4iLCJtYWMiOiI2MWIzNGYwZTdjZTkyZmUzZmRiNTBmOGExOGE1OWY0MzM0M2IxODMyMzE0NmYyMGIyMmQxMDNmYTM2Y2M1NDdlIiwidGFnIjoiIn0%3D; XSRF-TOKEN=eyJpdiI6Ijg4aDQ1RzJmcnRGV2R6OGNoNHVWR3c9PSIsInZhbHVlIjoiQXpJMG51bSs2RDFHV0VPQk5zRHlRTUYxWTRoVktZMEh3S3diWVZhWVp5L0duaHk5aVppTWZtODVjYXdTeTZNeEtqRjY3bWJBeEVNYjVYbmZnSFZYby9LTWZ0eFJCTkhYSkJUUzBLbEZYWGFLSWFhYnM4d0g3bG5hRXZnM1VneU8iLCJtYWMiOiJmMjUwYzM5MzRmNmQyYWY1ZTJjMmE5NTAzNjQxMGExZWUyNGUxYzE1ZWQxMWZkY2Y4YWIzZjA1NmUwYjY2ZWY0IiwidGFnIjoiIn0%3D; laravel_session=eyJpdiI6InNHd0kyMUlRNFdLcElSWmVCditraGc9PSIsInZhbHVlIjoic0NOWEdINkhMbnc1QkhTenlsK0JLWDJRUkxUSE1EV2dmSEd6RzdIVjA2VkYxUWhSc1RmZXpha241Z0xJd3ZWSjk5aTdhbWk2akV4Mi9GbzdzMjBtVEZYbU5FUlNNWHpYWWZyVTVxSDIxdDhXdnhOQ01pamRCRi82VzUyTGprRlEiLCJtYWMiOiJmNDcxM2FiYjE5YmM4MzkwM2U5M2RkYTk2Y2Q0MThjZjJmOGQxMTc3NDg5NGI4MmRlNTUxZTljMzgwY2QzOWRkIiwidGFnIjoiIn0%3D'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        if ($response)
            echo $response;
        else
            return ["status" => false, "massage" => 'جوابی از crm  دریافت نشد'];
    }


    public function getToken($id)
    {

        if (auth('api')->user()->company_id) {
            return ["status" => false, "code" => 403, "message" => "این امکان فقط برای مستر می باشد"];
        }

        $company = CompanyToken::where('company_id', $id)->first();
        if ($company && $company->token) {

            return  ['token' => $company->token];
        }
        if ($company)
            $token = CompanyToken::where('company_id', $id)->first();
        else
            $token = new CompanyToken();
        $randomString = Str::random(1000);
        $token->company_id = $id;
        $token->token = $randomString;
        $token->save();
        return  ['token' => $randomString];
    }
}
