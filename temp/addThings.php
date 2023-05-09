<?php

namespace App\Console\Commands;

use Cms\Packages\traverser\TraverserLicense;
use Fanap\Platform\Fanapium;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class addThings extends Command
{

    const LIMITED = 5000 ;


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'addThings:start {apiToken} {offset} {limit}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'php artisan addThings:start {apiToken}';

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
	$time = time();
	$count = 0;
	$duplicateRow = 0;
	$duplicateThing = 0;
        $rows = DB::table('traverse_ocrs')
            ->select('traverse_ocrs.license', 'traverse_ocrs.vehicle_class')
            ->leftJoin('traverser_licenses', 'traverse_ocrs.license', '=', 'traverser_licenses.license')
            ->whereNull('traverser_licenses.license')
	    ->offset($this->argument('offset'))
            ->limit($this->argument('limit'))
            ->get();
	echo "\n[" . date("F j, Y, g:i a") . "] offset-" . $this->argument('offset') . " : " . count($rows) . " rows selected\n";
        if ($rows->isNotEmpty()){
            foreach ($rows as $row) {
		$licenseRow = DB::table('traverser_licenses')
		    ->select('license')
		    ->where(['license' => $row->license])
		    ->get();
		if(count($licenseRow) > 0){
			//echo "offset-" . $this->argument('offset') . " : " . $row->license . " : duplicateRow \n";
			$duplicateRow++;
			continue;
		}
                $thing = $this->createThingsJob([
                    'name' => $row->license,
                    'type' => 'vehicle',
                    'metadata' => json_encode(['vehicle_class' => $row->vehicle_class])
                ]);
		
		if(!empty($thing->error)){
			if($thing->error == 'conflict_thing_username'){
				$duplicateThing++;
			}else{
				echo "\n error : " . $thing->error . "\n";
			}
			
		}

                if( ( !empty($thing->error) && $thing->error == 'conflict_thing_username' ) ||  !empty($thing->id) ){
                    try {
                        TraverserLicense::_()->insert(['license' => $row->license]);
			$count++;
                    } catch (\Exception $e) {

                    }
                }
            }
        }
	$time = time() - $time;
	echo "\n[" . date("F j, Y, g:i a") . "] offset-" . $this->argument('offset') . " inserted : $count duplicateRow : $duplicateRow time : $time duplicateThing : $duplicateThing \n</br></br>\n";
    }

    public function createThingsJob($thing)
    {
        //$requestUrl = 'https://accounts.pod.ir/things';
	$requestUrl = 'http://10.20.17.42:8080/things';
        $ch = curl_init($requestUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($thing));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization:Bearer {$this->argument('apiToken')}"]);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response);
    }
}
