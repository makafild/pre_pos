<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Cms\Packages\gateway_share\GatewayShare;
use Cms\Packages\issuer\Issuer;
use Cms\Packages\pod_business\PodBusiness;
use Cms\Packages\traverse_ocr\TraverseOcr;
use Cms\Packages\traverser\TraverserDelegationUse;
use Fanap\Platform\Fanapium;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class blackTraverseInvoice extends Command
{

    const ORC_SELECT_TYPE = 'accept' ;
    const LIMITED = 20000 ;
    const CASH_USER_ID = 1143103 ;


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blackTraverseInvoice:start';

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
        $prefix = config('database.connections.oracle.prefix');
        $gatewayShares = GatewayShare::_()->gatewayShares();
        $gateways = DB::table('gateways')->get();
        $gatewayArray = [];
        foreach($gateways as $gateway){
            $gatewayArray[$gateway->serial] = $gateway->name;
        }
        $selects = [
            'traverse_ocrs.id',
            'traverse_ocrs.status',
            'traverse_ocrs.event_id',
            'traverse_ocrs.license',
            'pod_businesses.api_token',
            'traverse_ocrs.id as ocr_id',
            'traverse_ocrs.vehicle_class',
            'traverse_ocrs.traverse_time',
            'traverse_ocrs.orginal_traverse_time as ocr_orginal_traverse_time',
            'toll_prices.product_id',
            'toll_prices.start_date',
            'toll_prices.price as gateway_price',
            'toll_prices.gateway_serial as gateway',
            'traverser_licenses.sso_id'
        ];
        $rows = DB::table('traverse_ocrs')
            ->select($selects)
            ->join('toll_prices', function ($join) {
                $join->on('toll_prices.gateway_serial', '=', 'traverse_ocrs.gateway_serial');
                $join->on('toll_prices.vehicle_class', '=', 'traverse_ocrs.vehicle_class');
            })
            ->join('traverser_licenses', 'traverse_ocrs.license', '=', 'traverser_licenses.license')
            ->join('gateway_shares', 'gateway_shares.gateway_serial', '=', 'traverse_ocrs.gateway_serial')
            ->join('pod_businesses', function($join){
                $join->on('gateway_shares.business_id', '=', 'pod_businesses.business_id');
                $join->where('pod_businesses.type','sepandar');
            })
            ->where([
                'traverse_ocrs.state' => self::ORC_SELECT_TYPE ,
                'traverse_ocrs.status' =>'black'
            ])
            ->limit(self::LIMITED)
            ->orderBy('traverse_ocrs.status','DESC')
            ->orderBy('traverse_ocrs.traverse_time','ASC')
            ->get();
        if ($rows->isNotEmpty()) {
            $ocrs=[];
            foreach ($rows as $row) {
                if (in_array($row->id, $ocrs)) {
                    continue;
                }
                $row = master::_()->multiTollPrice($rows, $row);
                $ocrs[] = $row->id;
                $totalPrice = 0 ;
                $invoiceShares = [] ;
                foreach ($gatewayShares[$row->gateway]['business'] as $share){
                    if( $share['type'] == 'price' ){
                        $invoiceShares[$share['business_id']]['amount'] = $share['amount'] ;
                        $invoiceShares[$share['business_id']]['type'] = $share['business_type'] ;
                        $totalPrice += $share['amount'];
                    }
                }
                $totalPercent = $row->gateway_price - $totalPrice ;
                foreach ($gatewayShares[$row->gateway]['business'] as $share){
                    if( $share['type'] == 'percent' ){
                        $invoiceShares[$share['business_id']]['amount'] = ( $totalPercent*$share['amount'] ) / 100 ;
                        $invoiceShares[$share['business_id']]['type'] = $share['business_type'] ;
                    }
                }
                $description = "قطعه : " . $gatewayArray[$row->gateway] . " - پلاک : " . $row->license;
                $blackInvoice = [
                    'userId' => self::CASH_USER_ID ,
                    'currencyCode' => 'IRR',
                    'addressId' => '0',
                    'preferredTaxRate' => '0' ,
                    'verificationNeeded' => 'false',
                    'preview' => 'false',
                    'description' => $description,
                    'customerInvoiceItemVOs' => [
                        [
                            'productId' => $row->product_id,
                            'price' => (int) $row->gateway_price,
                            'quantity' => '1',
                            'description' =>  $description
                        ]
                    ]
                ];
                foreach ($invoiceShares as $is => $invoiceShare){
                    if($invoiceShare['type'] == 'sepandar'){
                        $blackInvoice['mainInvoice'] = [
                            'guildCode' => 'TRANSPORTATION_GUILD' ,
                            'billNumber' => $row->ocr_id,
                            'description' => $description,
                            'invoiceItemVOs' => [
                                [
                                    'productId' => $row->product_id,
                                    'price' => 0,
                                    'quantity' => '1',
                                    'description' =>  $description
                                ]
                            ],
                        ];
                    }else{
                        $blackInvoice['subInvoices'][] = [
                            'businessId' => $is,
                            'guildCode' => 'TRANSPORTATION_GUILD',
                            'billNumber' => $row->ocr_id,
                            'description' => $description,
                            'invoiceItemVOs' => [
                                [
                                    'productId' => $row->product_id,
                                    'price' => $invoiceShare['amount'],
                                    'quantity' => '1',
                                    'description' =>  $description
                                ]
                            ],
                        ];
                    }
                }
                try{
                    $invoiceLog['price'] = $row->gateway_price;
                    $invoiceLog['ocr_id'] = $row->ocr_id;
                    $invoiceLog['user_id'] = self::CASH_USER_ID;
                    $invoiceLog['created_at'] = Carbon::now();
                    $invoiceLog['updated_at'] = Carbon::now();
                    $invoiceLog['share'] = json_encode($blackInvoice);
                    $cashToken = PodBusiness::_()->where(['user_id' => self::CASH_USER_ID])->first();
                    $invoiceLog['api_token'] = !empty($cashToken->api_token) ? $cashToken->api_token: '';
                    DB::table('invoice_logs')->insert($invoiceLog);
                }catch (\Exception $e){

                }
            }
        }
    }
}
