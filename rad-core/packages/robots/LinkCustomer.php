<?php

namespace Core\Packages\robots;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**

 */
class LinkCustomer
{


    public static function getRefrral($mobile,$telephone)
    {

        $company = CompanyToken::where('company_id', auth('api')->user()->company_id)->first();
        if (!$company || !auth('api')->user()->api_url || !$search) return null;


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => auth('api')->user()->api_url . '/linkCustomer',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
    "token":"' . $company->token . '",
    "mobile":"' . $mobile . '",
    "telephone":"' . $telephone . '",
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
            return null;


    }

}
