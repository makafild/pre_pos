<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Cms\Packages\pod_business\PodBusiness;

class smsClassification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'smsClassification:start {counter}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'smsClassification';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function handle()
    {
        $validation = [];
        $providerLicenses = DB::table('sms_message_queues')->whereNotNull('provider_id')->get();
        foreach ($providerLicenses as $providerLicense){
            $validation[$providerLicense->license] = $providerLicense->provider_id;
        }
        $chunk = !empty($this->argument('counter')) ? $this->argument('counter') : 5000;
        $division = 0;
        $bulkDivisions = [];
        $providers = PodBusiness::where('type', 'provider')->pluck('id')->toArray();
        foreach ($providers as $provider){
            $rows =  DB::table('sms_message_queues')
                ->where(['status' => 0])
                ->whereNull('provider_id')
                ->offset($division)
                ->limit($chunk)
                ->get();
            if($rows->isNotEmpty()){
                foreach ($rows as $row){
                    $providerId = !empty($validation[$row->license]) ? $validation[$row->license] : $provider;
                    $bulkDivisions[$providerId][] = $row->id;
                }
            }
            $division += $chunk  ;
        }
        if(!empty($bulkDivisions)){
            foreach ($bulkDivisions as $p => $bulkDivision){
                foreach ($bulkDivision as $value){
                    DB::table('sms_message_queues')->where('id',$value)->update([
                        'provider_id' => $p
                    ]);
                }
            }
        }
    }
}
