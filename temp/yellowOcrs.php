<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Fanap\Platform\Fanapium;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Cms\Packages\gateway_share\GatewayShare;
use Cms\Packages\yellow_ocr\YellowOcr;
use Cms\Packages\traverse_ocr\TraverseOcr;
use Cms\Packages\pod_business\PodBusiness;

class yellowOcrs extends Command
{
    const SELF_DECLARATION_DEADLINE_BEFORE_DAY = 10;
    const SELF_DECLARATION_DEADLINE_AFTER_DAY = 2;
    const SELF_DECLARATION_DEADLINE_SECOND = 10;
    const SELF_DECLARATION_DEADLINE_THIRD = 15;
    const LIMITED = 10;
    const LIMITDAY = 3;
    const MASKAN_ISSUER = 90628;   //maskan bank issuer_id



    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yellowOcrs:start';

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

    function traverseOcrStatus($id, $status)
    {
        return TraverseOcr::where('id', $id)->update(['status' => $status]);
    }

    function traverseOcrState($id, $status)
    {
        return TraverseOcr::where('id', $id)->update(['state' => $status]);
    }

    function yellowOcrStatus($ocr_id, $status)
    {
        return YellowOcr::where('ocr_id', $ocr_id)->update(['status' => $status]);
    }

    function blackGrayStatus($row)
    {
        $ocrVersion = DB::table('traverse_ocrs_versions')
            ->select('id', 'traverse_time')
            ->where('id', $row->id)
            ->where('version', 0)
            ->first();


        if ($row->version == 0) {
            $traverseTime = $row->traverse_time;
            $first_time = strtotime('+' . self::SELF_DECLARATION_DEADLINE_AFTER_DAY . ' days', strtotime($row->traverse_time));
            $second_time = strtotime('+' . self::SELF_DECLARATION_DEADLINE_SECOND . ' days', strtotime($row->traverse_time));
            $third_time = strtotime('+' . self::SELF_DECLARATION_DEADLINE_THIRD . ' days', strtotime($row->traverse_time));
        } elseif (!empty($ocrVersion)) {
            $traverseTime = $ocrVersion->traverse_time;
            $first_time = strtotime('+' . self::SELF_DECLARATION_DEADLINE_AFTER_DAY . ' days', strtotime($ocrVersion->traverse_time));
            $second_time = strtotime('+' . self::SELF_DECLARATION_DEADLINE_SECOND . ' days', strtotime($ocrVersion->traverse_time));
            $third_time = strtotime('+' . self::SELF_DECLARATION_DEADLINE_THIRD . ' days', strtotime($ocrVersion->traverse_time));
        } else {
            \Log::error('version_doesnt_exists =>  ' . $row->id);
            return false;
        }

        $checkPassedDate = Carbon::now()->timestamp;
        $status = '';

        /*
         *  from traverseTime sms1
         * +48 hours sms2  from traverseTime
         * +10 days from traverseTime  sms3
         * +15 days from traverseTime =>black
         */

        $message = [
//            'sso_id' => $row->sso_id ,
            'license' => $row->license,
            'message' => json_encode([
                'license' => $row->license,
                'vehicle_class' => $row->vehicle_class,
                'event_id' => $row->event_id,
                'price' => $row->gateway_price,
                'traverse_time' => $traverseTime,
                'gateway' => $row->gateway
            ]),
        ];
        if (is_null($row->status)) {
            $status = 'gray';
            $message['type'] = 'sepandar1';
        } elseif ($row->status == 'gray') {
            if ($third_time <= $checkPassedDate) {
                $status = 'pink';
                $message['type'] = 'sepandar3';
            } elseif ($second_time <= $checkPassedDate) {
                $status = 'brown';
                $message['type'] = 'sepandar2';
            } elseif ($first_time <= $checkPassedDate) {
                $status = 'black';
                $message['type'] = 'police';
                //Call Api Police
            }
        }
        if (!empty($status)) {
            $this->traverseOcrStatus($row->id, $status);
            if ($row->state !== 'yellow' && count($message) > 0) {
                BaseModel::createSmsMessageQueues($message);
            }
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $gatewayShares = GatewayShare::_()->gatewayShares();
        $gateways = DB::table('gateways')->get();
        $gatewayArray = [];
        foreach ($gateways as $gateway) {
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
            'traverser_states.issuer_id',
            'traverser_states.issuer_user_id',
            'traverse_ocrs.traverse_time',
            DB::raw('p.product_id'),
            DB::raw('p.price as gateway_price'),
            DB::raw('p.gateway_serial as gateway'),
            'traverser_states.delegation',
            'traverser_states.state',
            'traverser_licenses.sso_id',
            'traverse_ocrs.version',
            'yellow_ocrs.created_at as yellowocr_created_at',
            DB::raw('du.used')
        ];
        $prefix = config('database.connections.oracle.prefix');

        $tollPrices = "select GATEWAY_SERIAL, VEHICLE_CLASS, START_DATE,PRICE,PRODUCT_ID, row_number() over (partition by GATEWAY_SERIAL,VEHICLE_CLASS order by START_DATE desc ) as rn
                    from {$prefix}TOLL_PRICES where TO_CHAR(start_date , 'YYYYMMDD') <= TO_CHAR(SYSDATE, 'YYYYMMDD')";
        $traverserDelegationUses = DB::table('traverser_delegation_uses')->select('sso_id', DB::raw('SUM(price) as used'))->groupBy('sso_id');
        $traverseOcrs = DB::table('traverse_ocrs')
            ->select($selects)
            ->join(DB::raw("({$tollPrices}) p"),
                function ($join) {
                    $join->on('traverse_ocrs.gateway_serial', '=', DB::raw('p.gateway_serial'));
                    $join->on('traverse_ocrs.vehicle_class', '=', DB::raw('p.vehicle_class'));
                }
            )
            ->join('traverser_licenses', 'traverse_ocrs.license', '=', 'traverser_licenses.license')
            ->join('gateway_shares', 'gateway_shares.gateway_serial', '=', 'traverse_ocrs.gateway_serial')
            ->join('pod_businesses', function ($join) {
                $join->on('gateway_shares.business_id', '=', 'pod_businesses.business_id');
                $join->where('pod_businesses.type', 'sepandar');
            })
            ->leftJoin('traverser_states', function ($join) {
                $join->on('traverser_states.sso_id', '=', 'traverser_licenses.sso_id');
                $join->where('traverser_states.issuer_id', self::MASKAN_ISSUER);
            })
            ->join('yellow_ocrs', 'yellow_ocrs.ocr_id', '=', 'traverse_ocrs.id')
            ->leftJoin(DB::raw('(' . $traverserDelegationUses->toSql() . ') du'),
                function ($traverserDelegationUses)  {
                    $join->on('traverser_states.sso_id', '=', DB::raw('du.sso_id'));
                }
            )
            ->where('traverse_ocrs.state', 'pending')
            ->where('yellow_ocrs.status', 'pending')
            ->where(DB::raw('p.rn'), 1)
            ->limit(self::LIMITED)
            ->get();
        if ($traverseOcrs->isNotEmpty()) {
            foreach ($traverseOcrs as $row) {
                //call api
                $callApi = 'nok';
                DB::beginTransaction();
                try {
                    if ($callApi == 'ok') {
                        //its mean this license has cash and then  must Lower the money
                        $this->traverseOcrState($row->id, 'accept');
                        $this->traverseOcrStatus($row->id, 'white');

                        /*
                         * issue factor
                         */
                        $totalPrice = 0;
                        $invoiceShares = [];
                        foreach ($gatewayShares[$row->gateway]['business'] as $share) {
                            if ($share['type'] == 'price') {
                                $invoiceShares[$share['business_id']]['amount'] = $share['amount'];
                                $invoiceShares[$share['business_id']]['type'] = $share['business_type'];
                                $totalPrice += $share['amount'];
                            }
                        }
                        $totalPercent = $row->gateway_price - $totalPrice;
                        foreach ($gatewayShares[$row->gateway]['business'] as $share) {
                            if ($share['type'] == 'percent') {
                                $invoiceShares[$share['business_id']]['amount'] = ($totalPercent * $share['amount']) / 100;
                                $invoiceShares[$share['business_id']]['type'] = $share['business_type'];
                            }
                        }
                        $description = "قطعه : " . $gatewayArray[$row->gateway] . " - پلاک : " . $row->license;
                        $issueInvoice = [
                            'userId' => (int)$row->issuer_user_id,
                            'currencyCode' => 'IRR',
                            'addressId' => '0',
                            'preferredTaxRate' => '0',
                            'verificationNeeded' => 'false',
                            'preview' => 'false',
                            'description' => $description,
                            'customerInvoiceItemVOs' => [
                                [
                                    'productId' => $row->product_id,
                                    //'price' => (int) $row->gateway_price,
                                    'price' => "auto",
                                    'quantity' => '1',
                                    'description' => $description
                                ]
                            ]
                        ];
                        foreach ($invoiceShares as $is => $invoiceShare) {
                            if ($invoiceShare['type'] == 'sepandar') {
                                $issueInvoice['mainInvoice'] = [
                                    'guildCode' => 'TRANSPORTATION_GUILD',
                                    'billNumber' => $row->ocr_id,
                                    'description' => $description,
                                    'invoiceItemVOs' => [
                                        [
                                            'productId' => $row->product_id,
                                            //'price' => $invoiceShare['amount'],
                                            'price' => 0,
                                            'quantity' => '1',
                                            'description' => $description
                                        ]
                                    ],
                                ];
                            } else {
                                $issueInvoice['subInvoices'][] = [
                                    'businessId' => $is,
                                    'guildCode' => 'TRANSPORTATION_GUILD',
                                    'billNumber' => $row->ocr_id,
                                    'description' => $description,
                                    'invoiceItemVOs' => [
                                        [
                                            'productId' => $row->product_id,
                                            //'price' => $invoiceShare['amount'],
                                            'price' => "auto",
                                            'quantity' => '1',
                                            'description' => $description
                                        ]
                                    ],
                                ];
                            }
                        }
                        $invoiceLog['price'] = $row->gateway_price;
                        $invoiceLog['ocr_id'] = $row->ocr_id;
                        $invoiceLog['user_id'] = $row->issuer_user_id;
                        $invoiceLog['created_at'] = Carbon::now();
                        $invoiceLog['updated_at'] = Carbon::now();
                        $invoiceLog['share'] = json_encode($issueInvoice);
                        $issuerToken = PodBusiness::_()->where(['user_id' => $row->issuer_user_id])->first();
                        $invoiceLog['api_token'] = !empty($issuerToken->api_token) ? $issuerToken->api_token : '';
                        DB::table('invoice_logs')->insert($invoiceLog);
                        /*
                         * end of issue factor
                         */

                        $this->yellowOcrStatus($row->id, 'accept');
                    } elseif ($callApi == 'pending') {
                        $first_time = strtotime('+' . self::LIMITDAY . ' days', strtotime($row->yellowocr_created_at));
                        $checkPassedDate = Carbon::now()->timestamp;
                        if ($first_time < $checkPassedDate) {
                            $this->blackGrayStatus($row);//black
                            $this->yellowOcrStatus($row->id, 'reject');
                        }
                    } elseif ($callApi == 'nok') {
                        $this->traverseOcrState($row->id, 'accept');
                        $this->blackGrayStatus($row);//gray
                        $this->yellowOcrStatus($row->id, 'reject');
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollback();
                }
            }
        }

    }
}
