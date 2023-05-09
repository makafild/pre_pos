<?php

namespace App\Console\Commands;

use Fanap\Platform\shared\Police;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class sendSms extends Command
{

    const LIMITED = 1000;


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sendSms:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
	for($i = 1; $i <= 6; $i++){
		$rows = DB::table('sms_message_queues')
		    ->where(['status' => 0, 'type' => 'police'])
		    ->limit(self::LIMITED)
		    ->get();
		if ($rows->isNotEmpty()) {
		    $messages = [];
		    foreach ($rows as $row) {
			$data = json_decode($row->message);
			$data->message_id = $row->id;
		        $messages[] = $data;
		    }
		    //var_dump($messages);
		    $log = "\n[" . date("Y/m/d H:i:s") . "] - ";
		    $count = count($messages);
		    $startTime = time();
		    $inserted = 0;
		    try {
			$policeTime = time();
		        $policeResults = Police::_()->sendSmsRecord($messages);
			$policeTime = time() - $policeTime;
			echo "\n policeTime : $policeTime\n";
			//var_dump($policeResults);
		        if(!empty($policeResults)){
			    $n = 0;
		            foreach ($policeResults as $policeResult) {
		                if(!empty($policeResult->id)){
				    //echo "\n " . ($n++) . " : " . $policeResult->id;
		                    DB::table('sms_message_queues')->where('id',$policeResult->message_id)->update([
		                        'status' => $policeResult->id,
		                        'send_date' => Carbon::now()
		                    ]);
				    $inserted++;
		                }else{
					var_dump($policeResults);
				}
		            }
		        }else{
				var_dump($policeResults);
			}
		    } catch ( \Exception $e) {
		    }
		    $time = time() - $startTime;
		    echo $log . "count : $count inserted ; $inserted time : $time s\n";
		}
	}
    }
}
