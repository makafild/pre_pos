<?php

namespace App\Console\Commands;

use Fanap\Platform\Fanapium;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class expireTickets extends Command
{

    private $maxLimits = 200 ;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'expireTickets:start';

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
        $time = microtime(true);
        $compareDateTime = Carbon::now()->timezone('Asia/Tehran')->getTimestamp() ;
        $rows = Fanapium::_()->searchTimelineByMetadata(4096,[
            'field' => 'ticket.state' ,
            'is' => 'active' ,
            'and' => [
                [
                    'field' => 'ticket.unix_endTime',
                    'lt' => $compareDateTime
                ]
            ]
        ],['offset' => 0 , 'size' => $this->maxLimits]);
        if( $rows->hasError == true ){
            return $rows;
        }
        if(!empty($rows->result)){
            foreach ($rows->result as $row){
                $data['tags'] = $row->tags;
                if(!empty($row->metadata)){
                    $metaData = json_decode($row->metadata);
                    if(!empty($metaData->ticket)){
                        $metaData->ticket->state = 'expired' ;
                        $metaData->ticket->statusModifierUserId = 1815607 /* userId */;
                        $metaData->ticket->statusModifierId = 8154786 /* ssoId */;
                        $metaData->ticket->statusModifierDateime = time();
                        $data['metadata'] = json_encode([
                            'ticket' => $metaData->ticket
                        ]);
                        $data['name'] = $row->name;
                        $data['version'] = $row->version;
                        $data['content'] = $row->data;
                        $response = Fanapium::_()->createPost($data, $row->entityId);
                        if ($response->hasError == true) {
                            echo "Ticket EntityId : " . $row->entityId . "Ignore \n" ;
                            return $response;
                        }
                        echo "Ticket EntityId : " . $row->entityId . " Expired \n"  ;
                    }
                }
            }
        }
        echo " time : " . (microtime(true) - $time) . " s";
    }
}
