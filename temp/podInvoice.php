<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Cms\Packages\pod_business\PodBusiness;
use Fanap\Platform\Fanapium;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class podInvoice extends Command
{

    const LIMITED = 60;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'podInvoice:start';

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
        $multiServicesSameTX = [];

        $podInvoices = DB::table('invoice_logs')->where([
            'invoice_reference' => 0,
            'state' => 'white'
        ])->get();

        if ($podInvoices->isNotEmpty()) {
            foreach ($podInvoices as $p => $podInvoice) {

                $business = PodBusiness::where('business_id', $podInvoice->business_id)->first();

                Fanapium::_()->addDealer(['_token_' => $business->api_token , 'dealerBizId' => 5664]);
                Fanapium::_()->addDealer(['_token_' => '980b80a08f8241029055fd2260f1ad7c', 'dealerBizId' => $podInvoice->business_id]);

                Fanapium::_()->addDealerProductPermission(['productId' => $podInvoice->product_id, 'dealerBizId' => $podInvoice->business_id]);
                $multiServicesSameTX[$podInvoice->ocr_id][$p] = json_decode($podInvoice->share);
            }
        }

        if (!empty($multiServicesSameTX)) {
            foreach ($multiServicesSameTX as $ocrId => $issueInvoice) {
                sort($issueInvoice);
                $response = Fanapium::_()->multiServicesSameTX($issueInvoice);
                if(!empty($response->referenceNumber)){
                    if(!empty($response->result) && is_array($response->result)){
                        foreach($response->result as $invoice){
                            $invoiceJson = json_decode($invoice);
                            DB::table('invoice_logs')->where([
                                'bill_number'=>$invoiceJson->result->billNumber])->update([
                                'invoice_id' =>  $invoiceJson->result->id,
                                'invoice_reference' => $response->referenceNumber
                            ]);
                        }
                    }
                }
            }
        }

    }
}

