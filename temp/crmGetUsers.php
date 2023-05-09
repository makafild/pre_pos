<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class crmGetUsers extends Command
{

    protected $signature = 'crmGetUsers:start';

    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        dd(master::_()->gett());
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://crmapi.testit.ir/Accounts/api/accounts",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Connection: keep-alive",
                "Pragma: no-cache",
                "Cache-Control: no-cache",
                "accept: text/plain",
                "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.85 Safari/537.36",
                "Referer: http://crmapi.testit.ir/swagger/index.html",
                "Accept-Language: en-US,en;q=0.9"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
       if(!empty($response)){
           dd(json_decode($response));
       }
    }

}
