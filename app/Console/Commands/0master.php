<?php

namespace App\Console\Commands;

use App\Models\Setting\City;
use App\Models\Setting\Province;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\BaseModel;


class master extends Command
{

    private static $url = "http://crmapi.testit.ir/Accounts/api/accounts";
    private static $_instance = null;

    protected $signature = 'master functions';

    protected $description = '';

    public static function _()
    {
        if (self::$_instance == null) {
            self::$_instance = new master;
        }
        return self::$_instance;
    }

    public function get()
    {
        $username = 'test';
        $password = 'test';
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_USERPWD => $username . ":" . $password,
            CURLOPT_URL => self::$url,
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

    function post($data)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => self::$url,
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

    function GetAllCrmProvinces()
    {
        $provinces = [];
        $rows = Province::get();
        if (!empty($row)) {
            foreach ($rows as $row) {
                $provinces[$row['bmsd_locationbaseId']] = $row['id'];
            }
        }
        return $provinces;
    }

    function GetAllCrmCities()
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

    function  GetAllCrmActivityCategory()
    {
        return [
            '279640000' => '834',# 'اسباب بازی'
            '279640001' => '833',#'کتاب فروشی'
            '279640002' => '835',#'لوازم التحریر'
            '279640003' => '836',#'مولتی'
        ];
    }
}
