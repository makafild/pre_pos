<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Core\Packages\user\Users;

class crmPostUsers extends Command
{

    protected $signature = 'crmPostUsers:start';

    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $checkUsers = Users::select(['users.*'])
            ->join('log_api_crm', function ($join) {
                $join->on('users.mobile_number', '!=', 'log_api_crm.mobile');
            })->limit(5)->orderBy('id', 'DESC')->get();
        if ($checkUsers->isNotEmpty()) {
            foreach ($checkUsers as $user) {
                if (!empty($user['mobile_number'])) {
                    $data = [
                        'name' => $user['last_name'],
                        'mobile' => $user['mobile_number']
                    ];

                    $sendData = master::_()->post($data);
                    if ($sendData->isSucceed && !empty($sendData->content)) {
                        //TODO
                        $logApiCrmData = [
                            'mobile' => $user['mobile_number'],
                            'input' => json_encode($data),
                            'output' => json_encode($sendData),
                            'status' => 'post',
                            'referral_id' => $sendData->content->id,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ];
                        DB::table('log_api_crm')->insert($logApiCrmData);
                        sleep(30);
                    }
                }
            }
        }
    }
}
